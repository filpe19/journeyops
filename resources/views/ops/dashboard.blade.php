@extends('layouts.app')

@section('title', 'Operations')

@section('content')
    <div class="mb-6 flex items-baseline justify-between">
        <h1 class="text-2xl font-bold">Operations</h1>
        <p class="text-xs text-slate-500">Window: last {{ $summary['window_days'] }} days · generated {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-6">
        @foreach ([
            'Paid orders today' => $summary['orders_today'],
            'Paid orders (window)' => $summary['orders_in_window'],
            'Revenue (window)' => money($summary['revenue_in_window']),
            'Journeys' => $summary['journeys_in_window'],
            'Completed' => $summary['journeys_completed'],
            'Abandoned' => $summary['journeys_abandoned'],
            'No checkout' => $summary['journeys_no_checkout'],
            'Active' => $summary['journeys_active'],
            'New buyers' => $summary['new_buyers'],
            'New producers' => $summary['new_producers'],
        ] as $label => $value)
            <div class="card p-4">
                <p class="text-xs text-slate-500">{{ $label }}</p>
                <p class="mt-1 text-xl font-bold">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mb-2 mt-8 font-semibold">Journeys by source</h2>
    <div class="flex flex-wrap gap-2 text-sm">
        <a href="{{ route('ops.dashboard') }}" class="rounded-full border px-3 py-1 {{ $source ? 'bg-white' : 'bg-indigo-600 text-white' }}">all</a>
        @foreach ($summary['journeys_by_source'] as $name => $count)
            <a href="{{ route('ops.dashboard', ['source' => $name]) }}" class="rounded-full border px-3 py-1 {{ $source === $name ? 'bg-indigo-600 text-white' : 'bg-white' }}">{{ $name }} · {{ $count }}</a>
        @endforeach
    </div>

    <h2 class="mb-2 mt-8 font-semibold">Recent journeys</h2>
    <div class="card overflow-x-auto p-0">
        <table class="w-full text-xs">
            <thead class="border-b bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-3 py-2">Journey</th><th class="px-3 py-2">Started</th><th class="px-3 py-2">Source</th>
                    <th class="px-3 py-2">User</th><th class="px-3 py-2">Role</th><th class="px-3 py-2">Last event</th>
                    <th class="px-3 py-2">Outcome</th><th class="px-3 py-2">Sequence</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($journeys as $j)
                    <tr class="border-b align-top last:border-0">
                        <td class="px-3 py-2 font-mono"><a class="text-indigo-600" href="{{ route('ops.journeys.show', $j['uuid']) }}">{{ $j['code'] }}</a></td>
                        <td class="whitespace-nowrap px-3 py-2">{{ $j['started_at']->format('m-d H:i') }}</td>
                        <td class="px-3 py-2">{{ $j['source'] }}</td>
                        <td class="px-3 py-2">{{ $j['user'] ?? 'anonymous' }}</td>
                        <td class="px-3 py-2">{{ $j['role'] ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $j['last_event'] }}</td>
                        <td class="px-3 py-2">
                            <span @class([
                                'rounded px-2 py-0.5 font-semibold',
                                'bg-emerald-100 text-emerald-800' => $j['outcome'] === 'completed',
                                'bg-amber-100 text-amber-800' => $j['outcome'] === 'abandoned',
                                'bg-slate-100 text-slate-700' => in_array($j['outcome'], ['no_checkout', 'active']),
                            ])>{{ $j['outcome'] }}</span>
                        </td>
                        <td class="px-3 py-2 font-mono text-[11px] text-slate-600">{{ $j['sequence'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">No journeys recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mb-2 mt-8 font-semibold">Recent orders</h2>
    <div class="card overflow-x-auto p-0">
        <table class="w-full text-xs">
            <thead class="border-b bg-slate-50 text-left text-slate-500">
                <tr><th class="px-3 py-2">Order</th><th class="px-3 py-2">Created</th><th class="px-3 py-2">Event</th><th class="px-3 py-2">Buyer</th><th class="px-3 py-2">Qty</th><th class="px-3 py-2">Total</th><th class="px-3 py-2">Status</th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-b last:border-0">
                        <td class="px-3 py-2 font-mono">{{ $order->reference }}</td>
                        <td class="px-3 py-2">{{ $order->created_at->format('m-d H:i') }}</td>
                        <td class="px-3 py-2">{{ $order->event->slug }}</td>
                        <td class="px-3 py-2">{{ $order->user->email }}</td>
                        <td class="px-3 py-2">{{ $order->quantity }}</td>
                        <td class="px-3 py-2">{{ money($order->total) }}</td>
                        <td class="px-3 py-2">{{ $order->status->value }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
