<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\JourneySession;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_buyer_can_buy_a_ticket(): void
    {
        $event = Event::factory()->create(['price' => 4900]);
        $buyer = User::factory()->create();

        $this->actingAs($buyer)
            ->post("/events/{$event->slug}/buy", ['quantity' => 2])
            ->assertRedirect("/checkout/{$event->slug}");

        $this->get("/checkout/{$event->slug}")
            ->assertOk()
            ->assertSee('Pay $98.00');

        $response = $this->post("/checkout/{$event->slug}", ['quantity' => 2, 'cardholder' => 'Buyer']);

        $order = Order::sole();
        $response->assertRedirect("/orders/{$order->id}");

        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame(9800, $order->total);
        $this->assertSame($buyer->id, $order->user_id);
        $this->assertNotNull($order->paid_at);
    }

    public function test_purchase_journey_is_recorded_and_completed(): void
    {
        $event = Event::factory()->create();
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->get("/events/{$event->slug}?ref=share");
        $this->post("/events/{$event->slug}/buy", ['quantity' => 1]);
        $this->get("/checkout/{$event->slug}");
        $this->post("/checkout/{$event->slug}", ['quantity' => 1, 'cardholder' => 'Buyer']);

        $this->assertSame([
            'event_page_viewed',
            'buy_clicked',
            'checkout_started',
            'order_created',
            'payment_started',
            'payment_completed',
        ], $this->journeyEventNames());

        $journey = JourneySession::with('events')->sole();
        $this->assertSame('event_share', $journey->source);
        $this->assertSame($buyer->id, $journey->user_id);
        $this->assertNotNull($journey->ended_at);
        $this->assertSame(JourneySession::OUTCOME_COMPLETED, $journey->outcome());
    }

    public function test_guest_is_asked_to_sign_in_before_paying(): void
    {
        $event = Event::factory()->create();

        $this->post("/events/{$event->slug}/buy", ['quantity' => 1]);

        $this->get("/checkout/{$event->slug}")->assertRedirect('/login');

        $this->assertSame(['buy_clicked', 'checkout_started', 'auth_required'], $this->journeyEventNames());

        $this->get('/login')->assertOk()->assertSee('Sign in to finish buying your ticket for');
    }

    public function test_guest_cannot_place_an_order(): void
    {
        $event = Event::factory()->create();

        $this->post("/checkout/{$event->slug}", ['quantity' => 1, 'cardholder' => 'Anon'])->assertRedirect('/login');

        $this->assertSame(0, Order::count());
    }

    public function test_existing_customer_signing_in_at_checkout_returns_to_checkout(): void
    {
        $event = Event::factory()->create();
        $buyer = User::factory()->create(['email' => 'returning@example.test']);

        $this->get("/events/{$event->slug}?ref=share");
        $this->post("/events/{$event->slug}/buy", ['quantity' => 3]);
        $this->get("/checkout/{$event->slug}")->assertRedirect('/login');

        $this->post('/login', ['email' => 'returning@example.test', 'password' => 'password'])
            ->assertRedirect("/checkout/{$event->slug}");

        $this->get("/checkout/{$event->slug}")->assertOk()->assertSee('Pay $147.00');

        $this->post("/checkout/{$event->slug}", ['quantity' => 3, 'cardholder' => 'Returning']);

        $this->assertSame(1, $buyer->orders()->where('status', OrderStatus::Paid)->count());
        $this->assertContains('checkout_resumed', $this->journeyEventNames());
        $this->assertSame(1, JourneySession::count());
    }

    public function test_quantity_is_validated(): void
    {
        $event = Event::factory()->create();

        $this->post("/events/{$event->slug}/buy", ['quantity' => 50])->assertSessionHasErrors('quantity');
        $this->post("/events/{$event->slug}/buy", ['quantity' => 0])->assertSessionHasErrors('quantity');
    }

    public function test_cannot_buy_tickets_for_a_draft_event(): void
    {
        $event = Event::factory()->draft()->create();

        $this->actingAs(User::factory()->create())
            ->post("/events/{$event->slug}/buy", ['quantity' => 1])
            ->assertNotFound();
    }

    /**
     * Regression test for: guest who registers during checkout must become a buyer
     * and must be returned to the intended checkout flow — never to producer onboarding.
     *
     * Covers the exact journey observed in JRN-721B2F18:
     *   event_page_viewed → buy_clicked → checkout_started → auth_required
     *   → signup_started → signup_completed → checkout_resumed → order_created
     *   → payment_started → payment_completed
     */
    public function test_guest_who_registers_during_checkout_returns_to_checkout_as_buyer(): void
    {
        $event = Event::factory()->create(['price' => 4900]);

        // Guest starts the purchase funnel — this stores PurchaseIntent and sets intended URL.
        $this->post("/events/{$event->slug}/buy", ['quantity' => 2]);
        $this->get("/checkout/{$event->slug}")->assertRedirect('/login');

        // Guest chooses to register (no account_type submitted — form hides the field in checkout context).
        $this->post('/register', [
            'name' => 'New Buyer',
            'email' => 'newbuyer@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            // Intentionally no 'account_type' key, as the checkout registration form omits it.
        ])->assertRedirect("/checkout/{$event->slug}");   // must return to checkout, NOT /producer/onboarding

        // Verify the created account is a buyer.
        $user = User::where('email', 'newbuyer@example.test')->sole();
        $this->assertSame(UserRole::Buyer, $user->role);

        // Verify the user is NOT sent to producer onboarding.
        $this->assertFalse($user->isProducer());

        // Checkout page must be accessible and show the correct total.
        $this->get("/checkout/{$event->slug}")->assertOk()->assertSee('Pay $98.00');

        // Complete the purchase.
        $this->post("/checkout/{$event->slug}", ['quantity' => 2, 'cardholder' => 'New Buyer']);

        $order = Order::sole();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame(9800, $order->total);
        $this->assertSame($user->id, $order->user_id);

        // Assert the full journey event sequence, including checkout_resumed after auth.
        $eventNames = $this->journeyEventNames();
        $this->assertContains('signup_completed', $eventNames);
        $this->assertContains('checkout_resumed', $eventNames);
        $this->assertContains('order_created', $eventNames);
        $this->assertContains('payment_completed', $eventNames);
        $this->assertNotContains('producer_onboarding_viewed', $eventNames);

        // Assert signup_completed recorded user_role = 'buyer'.
        $signupEvent = \App\Models\JourneyEvent::where('event_name', 'signup_completed')->sole();
        $this->assertSame('buyer', $signupEvent->metadata['user_role']);
        $this->assertStringContainsString('/checkout/', $signupEvent->metadata['target_route']);
    }

    /**
     * Regression: the checkout-context buyer-enforcement must hold even when the deployment
     * is configured with ACCOUNT_DEFAULT_TYPE=producer. The purchase-context invariant must
     * not depend on environment configuration.
     */
    public function test_checkout_registration_forces_buyer_even_when_default_is_producer(): void
    {
        // Simulate an environment that has ACCOUNT_DEFAULT_TYPE=producer.
        config(['accounts.default_type' => 'producer']);

        $event = Event::factory()->create(['price' => 4900]);

        $this->post("/events/{$event->slug}/buy", ['quantity' => 1]);
        $this->get("/checkout/{$event->slug}")->assertRedirect('/login');

        $this->post('/register', [
            'name' => 'Forced Buyer',
            'email' => 'forcedbuyer@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            // No account_type — the checkout form omits it.
        ])->assertRedirect("/checkout/{$event->slug}");   // must still return to checkout

        $user = User::where('email', 'forcedbuyer@example.test')->sole();
        $this->assertSame(UserRole::Buyer, $user->role);
    }

    public function test_order_confirmation_is_only_visible_to_its_owner(): void
    {
        $event = Event::factory()->create();
        $owner = User::factory()->create();
        $order = Order::create([
            'reference' => 'ORD-TEST0001', 'event_id' => $event->id, 'user_id' => $owner->id,
            'quantity' => 1, 'status' => OrderStatus::Paid, 'total' => 4900,
        ]);

        $this->actingAs($owner)->get("/orders/{$order->id}")->assertOk()->assertSee('ORD-TEST0001');
        $this->actingAs(User::factory()->create())->get("/orders/{$order->id}")->assertNotFound();
    }
}
