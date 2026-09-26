<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Journey\JourneyTracker;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use App\Payments\SimulatedPaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PlaceOrder
{
    public function __construct(
        private readonly SimulatedPaymentGateway $gateway,
        private readonly JourneyTracker $journey,
    ) {}

    public function handle(User $buyer, Event $event, int $quantity): Order
    {
        $order = DB::transaction(fn () => Order::create([
            'reference' => 'ORD-'.strtoupper(Str::random(8)),
            'event_id' => $event->id,
            'user_id' => $buyer->id,
            'quantity' => $quantity,
            'status' => OrderStatus::Pending,
            'total' => $event->price * $quantity,
        ]));

        $this->journey->record('order_created', [
            'order_id' => $order->id,
            'order_reference' => $order->reference,
            'quantity' => $quantity,
            'total' => $order->total,
        ], $event);

        $this->journey->record('payment_started', [
            'order_id' => $order->id,
            'provider' => 'simulated',
        ], $event);

        $result = $this->gateway->charge($order);

        if (! $result['approved']) {
            throw new RuntimeException('Payment was not approved.');
        }

        $order->forceFill([
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ])->save();

        $this->journey->record('payment_completed', [
            'order_id' => $order->id,
            'order_reference' => $order->reference,
            'transaction_reference' => $result['transaction_reference'],
        ], $event);

        return $order;
    }
}
