@extends('layouts.app')

@section('title', 'My account')

@section('content')
    <h1 class="text-2xl font-bold">Hi, {{ $user->name }}</h1>
    <p class="text-sm text-slate-500">{{ $user->email }}</p>

    <h2 class="mb-3 mt-8 font-semibold">My tickets</h2>
    <div class="card p-0">
        <table class="w-full text-sm">
            <thead class="border-b bg-slate-50 text-left text-slate-500">
                <tr><th class="px-4 py-2">Order</th><th class="px-4 py-2">Event</th><th class="px-4 py-2">Qty</th><th class="px-4 py-2">Total</th><th class="px-4 py-2">Status</th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2"><a class="text-indigo-600" href="{{ route('orders.show', $order) }}">{{ $order->reference }}</a></td>
                        <td class="px-4 py-2">{{ $order->event->title }}</td>
                        <td class="px-4 py-2">{{ $order->quantity }}</td>
                        <td class="px-4 py-2">{{ money($order->total) }}</td>
                        <td class="px-4 py-2">{{ $order->status->value }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No tickets yet. <a class="text-indigo-600" href="{{ route('home') }}">Browse events</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
