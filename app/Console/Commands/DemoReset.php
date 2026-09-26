<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:reset {--force : Skip the confirmation prompt}')]
#[Description('Rebuild the local database and journey log from scratch with synthetic data')]
class DemoReset extends Command
{
    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This wipes the local database and storage/logs/journey.jsonl. Continue?', true)) {
            return self::FAILURE;
        }

        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);

        $this->newLine();
        $this->call('ops:summary', ['--days' => 7]);

        return self::SUCCESS;
    }
}
