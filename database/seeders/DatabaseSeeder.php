<?php

namespace Database\Seeders;

use App\Journey\JourneyLog;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(JourneyLog $log): void
    {
        $log->truncate();

        $this->call([
            CatalogSeeder::class,
            DemoTrafficSeeder::class,
        ]);
    }
}
