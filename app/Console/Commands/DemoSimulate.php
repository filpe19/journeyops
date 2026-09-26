<?php

namespace App\Console\Commands;

use App\Demo\TrafficScenarios;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('demo:simulate {scenario?* : One or more scenario names} {--all : Run every scenario} {--list : List scenarios} {--url= : Send traffic to a running server instead of in-process}')]
#[Description('Generate synthetic visitor traffic through the real web flows')]
class DemoSimulate extends Command
{
    public function handle(TrafficScenarios $scenarios): int
    {
        $catalog = TrafficScenarios::catalog();

        if ($this->option('list') || (! $this->option('all') && $this->argument('scenario') === [])) {
            $this->table(['Scenario', 'Description'], collect($catalog)->map(fn ($d, $n) => [$n, $d])->values()->all());

            return self::SUCCESS;
        }

        $names = $this->option('all') ? array_keys($catalog) : $this->argument('scenario');
        $rows = [];

        foreach ($names as $name) {
            try {
                $result = $scenarios->run($name, null, $this->option('url') ?: null);
                $journeys = collect($result->browser->journeyIds())->map(fn ($id) => 'JRN-'.strtoupper(substr(str_replace('-', '', $id), 0, 8)))->implode(', ');
                $rows[] = [$name, $result->email ?: 'anonymous', $result->browser->path(), $journeys];
            } catch (Throwable $e) {
                $rows[] = [$name, '-', 'ERROR: '.$e->getMessage(), ''];
            }
        }

        $this->table(['Scenario', 'Visitor', 'Final page', 'Journeys'], $rows);

        return self::SUCCESS;
    }
}
