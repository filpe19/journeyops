<?php

namespace App\Http\Controllers;

use App\Actions\Orders\PlaceOrder;
use App\Journey\JourneyTracker;
use App\Models\Event;
use App\Support\PurchaseIntent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function buy(Request $request, Event $event, JourneyTracker $journey): RedirectResponse
    {
        abort_unless($event->isPublished(), 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        PurchaseIntent::remember($request->session(), $event, (int) $validated['quantity']);

        $journey->record('buy_clicked', [
            'quantity' => (int) $validated['quantity'],
            'target_route' => '/checkout/'.$event->slug,
        ], $event);

        return redirect()->route('checkout.show', $event);
    }

    public function show(Request $request, Event $event, JourneyTracker $journey): View|RedirectResponse
    {
        abort_unless($event->isPublished(), 404);

        $session = $request->session();
        $quantity = PurchaseIntent::quantityFor($session, $event);

        if ($request->user() === null) {
            PurchaseIntent::remember($session, $event, $quantity);
            $session->put(PurchaseIntent::AWAITING_AUTH_KEY, true);

            $journey->record('checkout_started', ['quantity' => $quantity], $event);
            $journey->record('auth_required', [
                'target_route' => '/login',
                'expected_destination' => '/checkout/'.$event->slug,
            ], $event);

            return redirect()->guest(route('login'));
        }

        if ($session->pull(PurchaseIntent::AWAITING_AUTH_KEY)) {
            $journey->record('checkout_resumed', ['quantity' => $quantity], $event);
        } else {
            $journey->record('checkout_started', ['quantity' => $quantity], $event);
        }

        return view('checkout.show', [
            'event' => $event,
            'quantity' => $quantity,
            'total' => $event->price * $quantity,
        ]);
    }

    public function store(Request $request, Event $event, PlaceOrder $placeOrder, JourneyTracker $journey): RedirectResponse
    {
        abort_unless($event->isPublished(), 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'cardholder' => ['required', 'string', 'max:120'],
        ]);

        $order = $placeOrder->handle($request->user(), $event, (int) $validated['quantity']);

        PurchaseIntent::forget($request->session());
        $journey->end();

        return redirect()->route('orders.show', $order);
    }
}
