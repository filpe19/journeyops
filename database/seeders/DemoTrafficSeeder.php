<?php

namespace Database\Seeders;

use App\Demo\TrafficScenarios;
use App\Journey\IdleJourneyCloser;
use Illuminate\Database\Seeder;

/**
 * Replays a few days of synthetic visitor traffic through the real HTTP
 * stack (in-process), using a simulated clock so the history looks natural.
 */
class DemoTrafficSeeder extends Seeder
{
    public function run(TrafficScenarios $scenarios, IdleJourneyCloser $closer): void
    {
        $today = now()->startOfMinute();

        $plan = [
            [$today->copy()->subDays(3)->setTime(10, 5), 'member-signup-homepage', []],
            [$today->copy()->subDays(3)->setTime(14, 20), 'returning-buyer-homepage', []],
            [$today->copy()->subDays(2)->setTime(9, 15), 'organizer-signup', []],
            [$today->copy()->subDays(2)->setTime(11, 40), 'share-link-browse-only', []],
            [$today->copy()->subDays(2)->setTime(16, 5), 'signed-in-buyer-share-link', []],
            [$today->copy()->subDays(1)->setTime(10, 30), 'member-buys-from-share-link', []],
            [$today->copy()->subDays(1)->setTime(13, 10), 'share-link-leaves-at-sign-in', []],
            [$today->copy()->subDays(1)->setTime(18, 45), 'newsletter-buyer', []],
            [$today->copy()->subMinutes(330), 'returning-buyer-share-link', []],
            [$today->copy()->subMinutes(250), 'share-link-browse-only', []],
            [$today->copy()->subMinutes(185), 'signed-in-buyer-share-link', []],
            [$today->copy()->subMinutes(125), 'share-link-new-visitor-signup', ['name' => 'Riley Chen', 'email' => 'riley.chen@example.test']],
            [$today->copy()->subMinutes(70), 'returning-buyer-homepage', ['quantity' => '1']],
        ];

        foreach ($plan as [$at, $scenario, $options]) {
            $result = $scenarios->run($scenario, $at, null, $options);
            $this->command?->line(sprintf('  %s  %-32s %s', $at->format('Y-m-d H:i'), $scenario, $result->browser->path()));
        }

        $closed = $closer->close((int) config('journey.idle_minutes'));
        $this->command?->line("  closed {$closed['closed']} idle journeys");
    }
}
