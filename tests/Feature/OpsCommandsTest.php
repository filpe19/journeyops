<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\JourneySession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpsCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_and_journey_commands_run(): void
    {
        $event = Event::factory()->create();
        $this->get("/events/{$event->slug}?ref=share");
        $journey = JourneySession::sole();

        $this->artisan('ops:summary')->assertSuccessful()->expectsOutputToContain('event_share');
        $this->artisan('ops:journeys')->assertSuccessful()->expectsOutputToContain($journey->code());
        $this->artisan('ops:journey', ['id' => $journey->code()])->assertSuccessful()->expectsOutputToContain('event_page_viewed');
        $this->artisan('ops:journey', ['id' => $journey->uuid, '--json' => true])->assertSuccessful();
        $this->artisan('ops:journey', ['id' => 'JRN-00000000'])->assertFailed();
    }

    public function test_simulate_lists_scenarios(): void
    {
        $this->artisan('demo:simulate', ['--list' => true])->assertSuccessful()->expectsOutputToContain('returning-buyer-homepage');
    }
}
