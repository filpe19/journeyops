<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\JourneySession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_event_page_is_public(): void
    {
        $event = Event::factory()->create(['title' => 'Demo Night']);

        $this->get('/events/'.$event->slug)
            ->assertOk()
            ->assertSee('Demo Night')
            ->assertSee('Buy ticket')
            ->assertSee('Factory Productions');
    }

    public function test_draft_events_are_not_visible(): void
    {
        $event = Event::factory()->draft()->create();

        $this->get('/events/'.$event->slug)->assertNotFound();
    }

    public function test_homepage_lists_only_published_events(): void
    {
        Event::factory()->create(['title' => 'Visible Show']);
        Event::factory()->draft()->create(['title' => 'Hidden Show']);

        $this->get('/')->assertOk()->assertSee('Visible Show')->assertDontSee('Hidden Show');
    }

    public function test_share_link_visit_starts_an_event_share_journey(): void
    {
        $event = Event::factory()->create();

        $this->get('/events/'.$event->slug.'?ref=share')
            ->assertOk()
            ->assertHeader('X-Journey-Id');

        $journey = JourneySession::sole();
        $this->assertSame('event_share', $journey->source);
        $this->assertSame($event->id, $journey->event_id);
        $this->assertSame(['event_page_viewed'], $this->journeyEventNames());
    }

    public function test_direct_visit_is_attributed_to_direct_source(): void
    {
        $event = Event::factory()->create();

        $this->get('/events/'.$event->slug);

        $this->assertSame('direct', JourneySession::sole()->source);
    }
}
