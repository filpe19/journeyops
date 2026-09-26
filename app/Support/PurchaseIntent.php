<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Contracts\Session\Session;

/**
 * The ticket a visitor was about to buy, kept in the session while they
 * move between checkout and authentication pages.
 */
class PurchaseIntent
{
    public const SESSION_KEY = 'checkout.intent';

    public const AWAITING_AUTH_KEY = 'checkout.awaiting_auth';

    public static function remember(Session $session, Event $event, int $quantity): void
    {
        $session->put(self::SESSION_KEY, [
            'event_id' => $event->id,
            'event_slug' => $event->slug,
            'quantity' => $quantity,
        ]);
    }

    /**
     * @return array{event_id: int, event_slug: string, quantity: int}|null
     */
    public static function get(Session $session): ?array
    {
        return $session->get(self::SESSION_KEY);
    }

    public static function event(Session $session): ?Event
    {
        $intent = self::get($session);

        return $intent ? Event::published()->find($intent['event_id']) : null;
    }

    public static function quantityFor(Session $session, Event $event): int
    {
        $intent = self::get($session);

        return ($intent && $intent['event_id'] === $event->id) ? $intent['quantity'] : 1;
    }

    public static function forget(Session $session): void
    {
        $session->forget([self::SESSION_KEY, self::AWAITING_AUTH_KEY]);
    }
}
