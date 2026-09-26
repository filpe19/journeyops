<?php

namespace App\Console\Commands;

use App\Journey\OpsReport;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ops:summary {--days=1 : Size of the reporting window in days} {--json : Output JSON}')]
#[Description('Sales and journey health for the recent window')]
class OpsSummary extends Command
{
    public function handle(OpsReport $report): int
    {
        $s = $report->summary(max(1, (int) $this->option('days')));

        if ($this->option('json')) {
            $this->line(json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->twoColumnDetail('<fg=gray>Window</>', "last {$s['window_days']} day(s)");
        $this->components->twoColumnDetail('Paid orders today', (string) $s['orders_today']);
        $this->components->twoColumnDetail('Paid orders in window', (string) $s['orders_in_window']);
        $this->components->twoColumnDetail('Revenue in window', money($s['revenue_in_window']));
        $this->components->twoColumnDetail('Journeys in window', (string) $s['journeys_in_window']);
        $this->components->twoColumnDetail('  completed', (string) $s['journeys_completed']);
        $this->components->twoColumnDetail('  abandoned', (string) $s['journeys_abandoned']);
        $this->components->twoColumnDetail('  no checkout', (string) $s['journeys_no_checkout']);
        $this->components->twoColumnDetail('  active', (string) $s['journeys_active']);
        $this->components->twoColumnDetail('New buyers', (string) $s['new_buyers']);
        $this->components->twoColumnDetail('New producers', (string) $s['new_producers']);

        $this->newLine();
        $this->line('<options=bold>Journeys by source</>');
        foreach ($s['journeys_by_source'] as $source => $count) {
            $this->components->twoColumnDetail($source, (string) $count);
        }

        return self::SUCCESS;
    }
}
