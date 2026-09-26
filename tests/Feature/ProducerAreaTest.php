<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProducerAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_producer_completes_onboarding(): void
    {
        $producer = User::factory()->producer()->create();

        $this->actingAs($producer)->get('/producer')->assertRedirect('/producer/onboarding');
        $this->get('/producer/onboarding')->assertOk()->assertSee('Set up your organizer profile');

        $this->post('/producer/onboarding', ['display_name' => 'Night Owls'])->assertRedirect('/producer');

        $this->assertSame('Night Owls', $producer->fresh()->producerProfile->display_name);
        $this->get('/producer')->assertOk()->assertSee('Night Owls');
        $this->assertSame(['producer_onboarding_viewed', 'producer_onboarding_completed'], $this->journeyEventNames());
    }

    public function test_onboarded_producer_skips_onboarding_page(): void
    {
        $this->actingAs(User::factory()->producer('Done Already')->create())
            ->get('/producer/onboarding')
            ->assertRedirect('/producer');
    }

    public function test_producer_can_create_and_publish_an_event(): void
    {
        $producer = User::factory()->producer('Night Owls')->create();

        $this->actingAs($producer)->post('/producer/events', [
            'title' => 'Rooftop Jazz',
            'description' => 'Live jazz on the roof.',
            'price' => '35.50',
            'starts_at' => now()->addMonth()->format('Y-m-d\TH:i'),
        ])->assertRedirect('/producer');

        $event = Event::where('slug', 'rooftop-jazz')->sole();
        $this->assertSame(EventStatus::Draft, $event->status);
        $this->assertSame(3550, $event->price);

        $this->post("/producer/events/{$event->slug}/publish")->assertRedirect('/producer');
        $this->assertSame(EventStatus::Published, $event->fresh()->status);

        $this->get('/producer')->assertSee('/events/rooftop-jazz?ref=share');
    }

    public function test_producer_cannot_publish_someone_elses_event(): void
    {
        $event = Event::factory()->draft()->create();

        $this->actingAs(User::factory()->producer('Other')->create())
            ->post("/producer/events/{$event->slug}/publish")
            ->assertForbidden();
    }

    public function test_buyers_cannot_access_producer_area(): void
    {
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->get('/producer')->assertForbidden();
        $this->actingAs($buyer)->get('/producer/onboarding')->assertForbidden();
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/producer')->assertRedirect('/login');
    }
}
