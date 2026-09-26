<?php

namespace App\Console\Commands;

use App\Journey\OpsReport;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

#[Signature('ops:journey {id : Journey UUID or JRN-XXXXXXXX code} {--json : Output JSON}')]
#[Description('Full timeline of a single journey')]
class OpsJourney extends Command
{
    public function handle(OpsReport $report): int
    {
        $journey = $report->find($this->argument('id'));

        if ($journey === null) {
            $this->components->error('Journey not found (or code is ambiguous).');

            return self::FAILURE;
        }

        $journey->load(['events', 'user.producerProfile', 'event']);
        $row = $report->row($journey);

        if ($this->option('json')) {
            $this->line(json_encode([
                'journey' => Arr::except($row, ['started_at']) + [
                    'started_at' => $journey->started_at->toIso8601String(),
                    'ended_at' => $journey->ended_at?->toIso8601String(),
                    'entry_route' => $journey->entry_route,
                    'anonymous_actor_id' => $journey->anonymous_actor_id,
                ],
                'events' => $journey->events->map(fn ($e) => [
                    'occurred_at' => $e->occurred_at->toIso8601String(),
                    'event' => $e->event_name,
                    'route' => $e->route,
                    'user_id' => $e->user_id,
                    'metadata' => $e->metadata,
                ]),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->line("<options=bold>{$row['code']}</>  <fg=gray>{$journey->uuid}</>");
        $this->components->twoColumnDetail('Source', $journey->source);
        $this->components->twoColumnDetail('Entry route', (string) $journey->entry_route);
        $this->components->twoColumnDetail('Event', $journey->event?->slug ?? '-');
        $this->components->twoColumnDetail('User', $journey->user?->email ?? 'anonymous');
        $this->components->twoColumnDetail('Current user role', $journey->user?->role?->value ?? '-');
        $this->components->twoColumnDetail('Producer profile', $journey->user ? ($journey->user->producerProfile ? 'yes' : 'no') : '-');
        $this->components->twoColumnDetail('Started', $journey->started_at->format('Y-m-d H:i:s'));
        $this->components->twoColumnDetail('Ended', $journey->ended_at?->format('Y-m-d H:i:s') ?? 'open');
        $this->components->twoColumnDetail('Outcome', $row['outcome']);
        $this->newLine();

        $this->table(['Time', 'Event', 'Route', 'Role', 'Metadata'], $journey->events->map(fn ($e) => [
            $e->occurred_at->format('H:i:s'),
            $e->event_name,
            $e->route,
            $e->metadata['user_role'] ?? '-',
            json_encode(Arr::except($e->metadata ?? [], ['journey_uuid', 'source', 'user_role']), JSON_UNESCAPED_SLASHES),
        ])->all());

        return self::SUCCESS;
    }
}
