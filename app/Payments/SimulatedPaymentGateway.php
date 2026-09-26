<?php

namespace App\Payments;

use App\Models\Order;

/**
 * Local stand-in for a card processor. Approves every charge
 * deterministically; no network calls are made.
 */
class SimulatedPaymentGateway
{
    /**
     * @return array{approved: bool, transaction_reference: string}
     */
    public function charge(Order $order): array
    {
        return [
            'approved' => true,
            'transaction_reference' => 'SIM-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
        ];
    }
}
