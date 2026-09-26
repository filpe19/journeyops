<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_page_offers_both_account_types(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Buy tickets')
            ->assertSee('Sell tickets');
    }

    public function test_sell_page_links_to_organizer_signup(): void
    {
        $this->get('/sell')->assertOk()->assertSee('/register?type=producer', false);
    }

    public function test_buyer_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Casey Buyer',
            'email' => 'casey@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => 'buyer',
        ])->assertRedirect('/account');

        $user = User::where('email', 'casey@example.test')->sole();
        $this->assertSame(UserRole::Buyer, $user->role);
        $this->assertNull($user->producerProfile);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_producer_registration_leads_to_onboarding(): void
    {
        $this->post('/register', [
            'name' => 'Stage Crew',
            'email' => 'crew@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => 'producer',
        ])->assertRedirect('/producer/onboarding');

        $this->assertSame(UserRole::Producer, User::where('email', 'crew@example.test')->sole()->role);
    }

    /**
     * Pins the safe default: registration without an explicit account_type must produce
     * a buyer, not a producer. This catches any future regression where the config default
     * is changed back to 'producer'.
     */
    public function test_registration_without_account_type_defaults_to_buyer(): void
    {
        $this->post('/register', [
            'name' => 'Default User',
            'email' => 'defaultuser@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            // Intentionally no 'account_type' key.
        ])->assertRedirect('/account');  // buyer home, not /producer/onboarding

        $user = User::where('email', 'defaultuser@example.test')->sole();
        $this->assertSame(UserRole::Buyer, $user->role);
    }

    public function test_admin_accounts_cannot_be_self_registered(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => 'admin',
        ])->assertSessionHasErrors('account_type');

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.test']);
    }

    public function test_email_must_be_unique_and_password_confirmed(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);

        $this->post('/register', [
            'name' => 'Dup', 'email' => 'taken@example.test',
            'password' => 'password', 'password_confirmation' => 'different', 'account_type' => 'buyer',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_signup_is_tracked(): void
    {
        $this->get('/register');
        $this->post('/register', [
            'name' => 'Tracked', 'email' => 'tracked@example.test',
            'password' => 'password', 'password_confirmation' => 'password', 'account_type' => 'buyer',
        ]);

        $this->assertSame(['signup_started', 'signup_completed'], $this->journeyEventNames());
    }

    public function test_user_can_log_in_and_out(): void
    {
        $user = User::factory()->create(['email' => 'login@example.test']);

        $this->post('/login', ['email' => 'login@example.test', 'password' => 'password'])->assertRedirect('/account');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'login@example.test']);

        $this->post('/login', ['email' => 'login@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
