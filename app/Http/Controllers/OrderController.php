<?php

namespace App\Http\Controllers;

use App\Models\JourneyEvent;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        // Read-only: the journey that produced this order, shown on the confirmation page.
        $journey = JourneyEvent::where('event_name', 'order_created')
            ->where('metadata->order_id', $order->id)
            ->latest('id')
            ->first()
            ?->journeySession
            ?->load('events');

        return view('orders.show', [
            'order' => $order->load('event'),
            'journey' => $journey,
        ]);
    }
}
