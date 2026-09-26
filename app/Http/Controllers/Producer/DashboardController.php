<?php

namespace App\Http\Controllers\Producer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $events = $request->user()->events()
            ->withCount(['orders as paid_orders_count' => fn ($q) => $q->where('status', OrderStatus::Paid)])
            ->withSum(['orders as revenue' => fn ($q) => $q->where('status', OrderStatus::Paid)], 'total')
            ->orderBy('starts_at')
            ->get();

        return view('producer.dashboard', [
            'profile' => $request->user()->producerProfile,
            'events' => $events,
        ]);
    }
}
