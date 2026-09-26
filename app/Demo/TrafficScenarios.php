<?php

namespace App\Demo;

use App\Models\User;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Synthetic visitors that exercise the public site the same way a person
 * would: following links, pressing buttons and submitting forms.
 *
 * All identities are fictitious (example.test).
 */
class TrafficScenarios
{
    public const DEMO_EVENT = 'ai-builders-night-2026';

    public const PASSWORD = 'password';

    public function __construct(private readonly BrowserFactory $browsers) {}

    /**
     * @return array<string, string>
     */
    public static function catalog(): array
    {
        return [
            'returning-buyer-homepage' => 'Existing buyer signs in from the homepage and buys a ticket.',
            'signed-in-buyer-share-link' => 'Signed-in buyer opens the shared event link and buys.',
            'returning-buyer-share-link' => 'Existing buyer opens the shared link, signs in at checkout and buys.',
            'newsletter-buyer' => 'Signed-in buyer arrives from the newsletter link and buys.',
            'member-signup-homepage' => 'Visitor creates a buyer account from the homepage.',
            'member-buys-from-share-link' => 'Member opens the shared link later, signs in at checkout and buys.',
            'organizer-signup' => 'New organizer signs up from the "Sell tickets" page and completes onboarding.',
            'share-link-browse-only' => 'Visitor opens the shared link and leaves.',
            'share-link-leaves-at-sign-in' => 'Visitor starts checkout from the shared link and leaves at sign-in.',
            'share-link-new-visitor-signup' => 'New visitor opens the shared link, starts checkout and creates an account.',
        ];
    }

    /**
     * @param  array<string, string>  $options  e.g. ['email' => ..., 'name' => ...]
     */
    public function run(string $scenario, ?Carbon $at = null, ?string $baseUrl = null, array $options = []): ScenarioResult
    {
        if (! array_key_exists($scenario, self::catalog())) {
            throw new InvalidArgumentException("Unknown scenario [{$scenario}]");
        }

        $browser = $this->browsers->make($baseUrl, $at);
        $method = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $scenario))));
        $email = $this->{$method}($browser, $options);

        return new ScenarioResult($scenario, $browser, $email);
    }

    private function returningBuyerHomepage(SyntheticBrowser $b, array $o): string
    {
        $email = $o['email'] ?? 'buyer.one@example.test';

        $b->visit('/')->click('Sign in')->wait(20)
            ->press('Sign in', ['email' => $email, 'password' => self::PASSWORD])
            ->visit('/')->wait(15)
            ->click('AI Builders Night 2026')->wait(40)
            ->press('Buy ticket', ['quantity' => $o['quantity'] ?? '2']);

        $this->payIfOffered($b);

        return $email;
    }

    private function signedInBuyerShareLink(SyntheticBrowser $b, array $o): string
    {
        $email = $o['email'] ?? 'buyer.two@example.test';

        $b->visit('/login')->press('Sign in', ['email' => $email, 'password' => self::PASSWORD])
            ->wait(600)
            ->visit($this->shareLink())->wait(30)
            ->press('Buy ticket');

        $this->payIfOffered($b);

        return $email;
    }

    private function returningBuyerShareLink(SyntheticBrowser $b, array $o): string
    {
        $email = $o['email'] ?? 'buyer.four@example.test';

        $b->visit($this->shareLink())->wait(45)
            ->press('Buy ticket')->wait(25)
            ->press('Sign in', ['email' => $email, 'password' => self::PASSWORD]);

        $this->payIfOffered($b);

        return $email;
    }

    private function newsletterBuyer(SyntheticBrowser $b, array $o): string
    {
        $email = $o['email'] ?? 'buyer.five@example.test';

        $b->visit('/login')->press('Sign in', ['email' => $email, 'password' => self::PASSWORD])
            ->wait(120)
            ->visit('/events/'.self::DEMO_EVENT.'?ref=newsletter')->wait(50)
            ->press('Buy ticket', ['quantity' => '3']);

        $this->payIfOffered($b);

        return $email;
    }

    private function memberSignupHomepage(SyntheticBrowser $b, array $o): string
    {
        $email = $o['email'] ?? $this->availableEmail('buyer.three');

        $b->visit('/')->click('Sign in')->click('Create an account')->wait(60)
            ->press('Create account', [
                'name' => $o['name'] ?? 'Buyer Three',
                'email' => $email,
                'password' => self::PASSWORD,
                'password_confirmation' => self::PASSWORD,
            ]);

        return $email;
    }

    private function memberBuysFromShareLink(SyntheticBrowser $b, array $o): string
    {
        return $this->returningBuyerShareLink($b, ['email' => $o['email'] ?? 'buyer.three@example.test']);
    }

    private function organizerSignup(SyntheticBrowser $b, array $o): string
    {
        $email = $o['email'] ?? $this->availableEmail('producer.lumen');

        $b->visit('/sell')->wait(30)
            ->click('Create an organizer account')->wait(60)
            ->press('Create account', [
                'name' => $o['name'] ?? 'Lumen Collective',
                'email' => $email,
                'password' => self::PASSWORD,
                'password_confirmation' => self::PASSWORD,
            ])->wait(30)
            ->press('Continue to organizer dashboard', ['display_name' => $o['name'] ?? 'Lumen Collective']);

        return $email;
    }

    private function shareLinkBrowseOnly(SyntheticBrowser $b, array $o): string
    {
        $b->visit($this->shareLink());

        return '';
    }

    private function shareLinkLeavesAtSignIn(SyntheticBrowser $b, array $o): string
    {
        $b->visit($this->shareLink())->wait(35)->press('Buy ticket');

        return '';
    }

    private function shareLinkNewVisitorSignup(SyntheticBrowser $b, array $o): string
    {
        $email = $o['email'] ?? $this->availableEmail('new.visitor');

        $b->visit($this->shareLink())->wait(40)
            ->press('Buy ticket')->wait(10)
            ->click('Create an account')->wait(55)
            ->press('Create account', [
                'name' => $o['name'] ?? 'New Visitor',
                'email' => $email,
                'password' => self::PASSWORD,
                'password_confirmation' => self::PASSWORD,
            ]);

        $this->payIfOffered($b);

        return $email;
    }

    private function payIfOffered(SyntheticBrowser $b): void
    {
        if ($b->wait(20)->canPress('Pay')) {
            $b->press('Pay');
        }
    }

    private function shareLink(): string
    {
        return '/events/'.self::DEMO_EVENT.'?ref=share';
    }

    private function availableEmail(string $local): string
    {
        $candidate = "{$local}@example.test";

        for ($n = 2; User::where('email', $candidate)->exists(); $n++) {
            $candidate = "{$local}.{$n}@example.test";
        }

        return $candidate;
    }
}
