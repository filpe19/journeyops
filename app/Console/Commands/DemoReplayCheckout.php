<?php

namespace App\Console\Commands;

use App\Demo\TrafficScenarios;
use App\Journey\IdleJourneyCloser;
use App\Models\JourneySession;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:replay-checkout {--url= : Replay against a running server instead of in-process} {--email= : Email for the new visitor (default: next free new.visitor@example.test)}')]
#[Description('Replay: new visitor opens the shared event link, checks out and creates an account on the way')]
class DemoReplayCheckout extends Command
{
    /** Stages of a purchase that includes account creation, in order. */
    private const STAGES = [
        'event_page_viewed',
        'buy_clicked',
        'checkout_started',
        'auth_required',
        'signup_started',
        'signup_completed',
        'checkout_resumed',
        'order_created',
        'payment_started',
        'payment_completed',
    ];

    public function handle(TrafficScenarios $scenarios, IdleJourneyCloser $closer): int
    {
        $options = array_filter(['email' => $this->option('email'), 'name' => 'Replay Visitor']);
        $result = $scenarios->run('share-link-new-visitor-signup', null, $this->option('url') ?: null, $options);
        $browser = $result->browser;

        $uuid = $browser->journeyIds()[0] ?? null;
        $journey = $uuid ? JourneySession::where('uuid', $uuid)->first() : null;

        if ($journey === null) {
            $this->components->error('No journey was recorded for the replay.');

            return self::FAILURE;
        }

        // The visitor's browser is gone: close the journey as the idle sweeper would.
        $closer->close(0, $journey->uuid);
        $journey->refresh()->load('events');

        $names = $journey->eventNames();
        $user = User::with('producerProfile')->where('email', $result->email)->first();
        $orders = $user?->orders()->count() ?? 0;

        $this->newLine();
        $this->line("Journey: <options=bold>{$journey->code()}</>  <fg=gray>{$journey->uuid}</>");
        $this->line("Entry:   {$journey->entry_route}?ref=share (source: {$journey->source})");
        $this->line("Visitor: {$result->email}");
        $this->newLine();

        foreach (self::STAGES as $stage) {
            $seen = in_array($stage, $names, true);
            $this->line(sprintf('%s %s', str_pad($stage.' ', 28, '.'), $seen ? '<fg=green>OBSERVED</>' : '<fg=red>MISSING</>'));
        }

        $other = array_values(array_unique(array_diff($names, self::STAGES)));
        $this->newLine();
        $this->line('Other events: '.($other ? implode(', ', $other) : 'none'));
        $this->line('Browser path: '.collect($browser->history())->pluck('path')->map(fn ($p) => strtok($p, '?'))->implode(' -> '));
        $this->line('Final page:   '.$browser->path().' (HTTP '.$browser->status().')');
        $this->line('User role:    '.($user?->role?->value ?? 'n/a').'   producer profile: '.($user?->producerProfile ? 'yes' : 'no'));
        $this->line('Orders:       '.$orders);
        $this->newLine();

        $outcome = strtoupper($journey->outcome());
        $this->line('Outcome: '.($outcome === 'COMPLETED' ? "<fg=green;options=bold>{$outcome}</>" : "<fg=red;options=bold>{$outcome}</>"));
        $this->line("<fg=gray>Timeline: php artisan ops:journey {$journey->code()}</>");

        return $outcome === 'COMPLETED' ? self::SUCCESS : self::FAILURE;
    }
}
