<?php

namespace App\Console\Commands;

use Database\Seeders\CatalogSeeder;
use Database\Seeders\DemoTrafficSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:seed {--catalog-only : Only accounts and events, no visitor traffic}')]
#[Description('Seed synthetic accounts, events and a few days of visitor traffic into an empty database')]
class DemoSeed extends Command
{
    public function handle(): int
    {
        $this->call('db:seed', ['--class' => CatalogSeeder::class, '--force' => true]);

        if (! $this->option('catalog-only')) {
            $this->call('db:seed', ['--class' => DemoTrafficSeeder::class, '--force' => true]);
        }

        return self::SUCCESS;
    }
}
