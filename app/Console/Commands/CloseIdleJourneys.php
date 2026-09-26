<?php

namespace App\Console\Commands;

use App\Journey\IdleJourneyCloser;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('journeys:close-idle {--minutes= : Idle threshold (defaults to journey.idle_minutes)} {--journey= : Only this journey UUID}')]
#[Description('End journeys without recent activity and flag abandoned checkouts')]
class CloseIdleJourneys extends Command
{
    public function handle(IdleJourneyCloser $closer): int
    {
        $minutes = $this->option('minutes') !== null ? (int) $this->option('minutes') : (int) config('journey.idle_minutes');

        $result = $closer->close($minutes, $this->option('journey') ?: null);

        $this->components->info("Closed {$result['closed']} journey(s); {$result['abandoned']} flagged as abandoned.");

        return self::SUCCESS;
    }
}
