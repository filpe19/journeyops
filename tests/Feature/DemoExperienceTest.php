<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\JourneySession;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Presentation layer of the public JourneyOps demo (landing page, guided demo,
 * journey panel on the order confirmation). Business behaviour is covered elsewhere.
 */
class DemoExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_explains_journeyops_and_keeps_the_catalog(): void
    {
        Event::factory()->create(['title' => 'Catalog Show']);

        $this->get('/')
            ->assertOk()
            ->assertSee('The test suite was green.')
            ->assertSee('Launch live demo')
            ->assertSee('Catalog Show')
            ->assertSee('/login', false);

        $this->assertContains('landing_viewed', $this->journeyEventNames());
    }

    public function test_guided_demo_page_is_public_and_records_no_journey(): void
    {
        $this->get('/demo')
            ->assertOk()
            ->assertSee('Launch live ticket journey')
            ->assertDontSee('admin@example.test');

        $this->assertSame(0, JourneySession::count());
    }

    public function test_order_confirmation_shows_the_recorded_journey(): void
    {
        $event = Event::factory()->create();

        $this->get("/events/{$event->slug}?ref=share");
        $this->post("/events/{$event->slug}/buy", ['quantity' => 1]);
        $this->get("/checkout/{$event->slug}");
        $this->get('/register');
        $this->post('/register', [
            'name' => 'Panel Buyer',
            'email' => 'panel@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $this->get("/checkout/{$event->slug}");
        $this->post("/checkout/{$event->slug}", ['quantity' => 1, 'cardholder' => 'Panel Buyer']);

        $order = Order::sole();

        $this->get("/orders/{$order->id}")
            ->assertOk()
            ->assertSee('What the telemetry recorded')
            ->assertSee('COMPLETED')
            ->assertSee('checkout_resumed')
            ->assertSee('user_role=<span class="text-ok">buyer</span>', false);
    }
}
