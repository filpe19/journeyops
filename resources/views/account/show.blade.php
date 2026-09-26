@extends('layouts.app')

@section('title', 'My account')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Account</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Hi, {{ $user->name }}</h1>
            <p class="mt-1 text-sm text-ink-muted [overflow-wrap:anywhere]">{{ $user->email }} · <span class="font-mono">{{ $user->role->value }}</span></p>
        </div>
        <a class="btn-secondary" href="{{ route('home') }}">Back to JourneyOps</a>
    </div>

    <h2 class="mt-10 text-lg font-semibold">My tickets</h2>
    <div class="mt-4 grid grid-cols-1 gap-3">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface px-5 py-4 transition hover:border-signal/40 hover:bg-raised">
                <span class="min-w-0">
                    <span class="block truncate font-semibold">{{ $order->event->title }}</span>
                    <span class="mt-0.5 block font-mono text-xs text-ink-muted">{{ $order->reference }} · {{ $order->quantity }} {{ \Illuminate\Support\Str::plural('ticket', $order->quantity) }}</span>
                </span>
                <span class="flex items-center gap-3">
                    <span class="font-mono text-sm">{{ money($order->total) }}</span>
                    <span class="{{ $order->status->value === 'paid' ? 'chip-ok' : 'chip-muted' }}">{{ $order->status->value }}</span>
                </span>
            </a>
        @empty
            <div class="card text-center text-ink-muted">
                No tickets yet. <a class="text-signal hover:underline underline-offset-4" href="{{ route('home') }}#lab">Browse events</a>
            </div>
        @endforelse
    </div>
@endsection
