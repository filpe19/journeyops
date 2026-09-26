<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\JourneySession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/ops')->assertRedirect('/login');
    }

    public function test_buyers_and_producers_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/ops')->assertForbidden();
        $this->actingAs(User::factory()->producer('P')->create())->get('/ops')->assertForbidden();
    }

    public function test_admin_sees_sales_and_journeys(): void
    {
        $event = Event::factory()->create();
        $this->get("/events/{$event->slug}?ref=share");
        $journey = JourneySession::sole();

        $this->actingAs(User::factory()->admin()->create())
            ->get('/ops')
            ->assertOk()
            ->assertSee('Recent journeys')
            ->assertSee($journey->code())
            ->assertSee('event_share');

        $this->get('/ops/journeys/'.$journey->uuid)
            ->assertOk()
            ->assertSee('event_page_viewed');
    }

    public function test_admin_logs_in_to_ops(): void
    {
        User::factory()->admin()->create(['email' => 'ops@example.test']);

        $this->post('/login', ['email' => 'ops@example.test', 'password' => 'password'])->assertRedirect('/ops');
    }
}
