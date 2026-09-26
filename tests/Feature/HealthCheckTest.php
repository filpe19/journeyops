<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_reports_ok(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJson(['status' => 'ok', 'database' => 'ok'])
            ->assertJsonStructure(['status', 'application', 'database', 'timestamp']);
    }
}
