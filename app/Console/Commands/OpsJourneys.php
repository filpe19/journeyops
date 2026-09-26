<?php

namespace App\Console\Commands;

use App\Journey\OpsReport;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ops:journeys {--limit=25} {--source= : Filter by source (event_share, homepage, email, ...)} {--with-sequence : Show the event sequence} {--json : Output JSON}')]
#[Description('List recent user journeys')]
class OpsJourneys extends Command
{
    public function handle(OpsReport $report): int
    {
        $rows = $report->journeys((int) $this->option('limit'), $this->option('source') ?: null);

        if ($this->option('json')) {
            $this->line($rows->map(fn ($r) => array_merge($r, ['started_at' => $r['started_at']->toIso8601String()]))->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $headers = ['Journey', 'Source', 'User', 'Role', 'Started', 'Last event', 'Outcome'];
        if ($this->option('with-sequence')) {
            $headers[] = 'Sequence';
        }

        $this->table($headers, $rows->map(function (array $r) {
            $row = [
                $r['code'],
                $r['source'],
                $r['user'] ?? 'anonymous',
                $r['role'] ?? '-',
                $r['started_at']->format('Y-m-d H:i'),
                $r['last_event'],
                $r['outcome'],
            ];

            if ($this->option('with-sequence')) {
                $row[] = $r['sequence'];
            }

            return $row;
        })->all());

        $this->line('<fg=gray>Inspect one with: php artisan ops:journey JRN-XXXXXXXX</>');

        return self::SUCCESS;
    }
}
