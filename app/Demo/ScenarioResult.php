<?php

namespace App\Demo;

final class ScenarioResult
{
    public function __construct(
        public readonly string $scenario,
        public readonly SyntheticBrowser $browser,
        public readonly string $email,
    ) {}
}
