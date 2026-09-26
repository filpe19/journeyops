<?php

namespace Tests\Feature;

use App\Journey\JourneyLog;
use App\Models\Event;
use App\Models\JourneyEvent;
use App\Models\JourneySession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TelemetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_are_mirrored_to_the_jsonl_log(): void
    {
        $event = Event::factory()->create();

        $this->get("/events/{$event->slug}?ref=share");
        $this->post("/events/{$event->slug}/buy", ['quantity' => 1]);

        $lines = array_map('json_decode', array_filter(explode(PHP_EOL, File::get(app(JourneyLog::class)->path()))));

        $this->assertCount(2, $lines);
        $this->assertSame('event_page_viewed', $lines[0]->event);
        $this->assertSame('buy_clicked', $lines[1]->event);
        $this->assertSame('event_share', $lines[1]->source);
        $this->assertSame($lines[0]->journey_id, $lines[1]->journey_id);
        $this->assertSame('/events/'.$event->slug, $lines[1]->metadata->previous_route);
    }

    public function test_events_carry_role_and_journey_metadata(): void
    {
        $event = Event::factory()->create();
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->get("/events/{$event->slug}");

        $recorded = JourneyEvent::sole();
        $this->assertSame($buyer->id, $recorded->user_id);
        $this->assertSame('buyer', $recorded->metadata['user_role']);
        $this->assertSame(JourneySession::sole()->uuid, $recorded->metadata['journey_uuid']);
    }

    public function test_credentials_are_never_recorded(): void
    {
        User::factory()->create(['email' => 'secretive@example.test']);

        $this->get('/login');
        $this->post('/login', ['email' => 'secretive@example.test', 'password' => 'password']);

        $this->assertStringNotContainsString('"password', File::get(app(JourneyLog::class)->path()));
        foreach (JourneyEvent::all() as $event) {
            $this->assertArrayNotHasKey('password', $event->metadata);
            $this->assertArrayNotHasKey('_token', $event->metadata);
        }
    }

    public function test_idle_checkout_journeys_are_closed_as_abandoned(): void
    {
        $event = Event::factory()->create();

        $this->post("/events/{$event->slug}/buy", ['quantity' => 1]);
        $this->get("/checkout/{$event->slug}");

        $this->travel(45)->minutes();
        $this->artisan('journeys:close-idle')->assertSuccessful();

        $journey = JourneySession::with('events')->sole();
        $this->assertNotNull($journey->ended_at);
        $this->assertSame('journey_abandoned', $journey->events->last()->event_name);
        $this->assertSame(JourneySession::OUTCOME_ABANDONED, $journey->outcome());
    }

    public function test_idle_browsing_journeys_close_without_abandonment(): void
    {
        $event = Event::factory()->create();

        $this->get("/events/{$event->slug}");
        $this->travel(45)->minutes();
        $this->artisan('journeys:close-idle')->assertSuccessful();

        $journey = JourneySession::with('events')->sole();
        $this->assertSame(JourneySession::OUTCOME_NO_CHECKOUT, $journey->outcome());
        $this->assertNotContains('journey_abandoned', $journey->eventNames());
    }

    public function test_reopening_a_tracked_link_starts_a_new_journey(): void
    {
        $event = Event::factory()->create();

        $this->get('/');
        $this->get("/events/{$event->slug}?ref=share");

        $this->assertSame(['homepage', 'event_share'], JourneySession::orderBy('id')->pluck('source')->all());
    }
}
