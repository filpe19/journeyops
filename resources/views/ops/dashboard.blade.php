@extends('layouts.app')

@section('title', 'Operations')

@php
    // Visual tone per journey event: ok = purchase progress, warn = interruption, fail = loss, info = neutral step.
    $eventTone = static fn (string $name): string => match (true) {
        in_array($name, ['checkout_resumed', 'order_created', 'payment_started', 'payment_completed', 'login_completed', 'signup_completed', 'producer_onboarding_completed'], true) => 'ok',
        $name === 'auth_required' => 'warn',
        $name === 'journey_abandoned' => 'fail',
        default => 'info',
    };
    $outcomeChip = static fn (string $outcome): string => match ($outcome) {
        'completed' => 'chip-ok',
        'abandoned' => 'chip-fail',
        'active' => 'chip-info',
        default => 'chip-muted',
    };
    $segment = ['ok' => 'bg-ok', 'warn' => 'bg-warn', 'fail' => 'bg-fail', 'info' => 'bg-line-strong'];
@endphp

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Ops console</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Operations</h1>
        </div>
        <p class="font-mono text-xs text-ink-muted">window: last {{ $summary['window_days'] }} days · generated {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    {{-- Journey health --}}
    <section class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="Journey health">
        <div class="card p-5">
            <p class="text-xs text-ink-muted">Journeys (window)</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $summary['journeys_in_window'] }}</p>
        </div>
        <div class="card border-ok/25 p-5">
            <p class="text-xs text-ink-muted">Completed</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-ok">{{ $summary['journeys_completed'] }}</p>
        </div>
        <div class="card border-fail/25 p-5">
            <p class="text-xs text-ink-muted">Abandoned</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-fail">{{ $summary['journeys_abandoned'] }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-ink-muted">No checkout · active</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $summary['journeys_no_checkout'] }} <span class="text-ink-muted">·</span> {{ $summary['journeys_active'] }}</p>
        </div>
    </section>

    <section class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-5" aria-label="Sales">
        @foreach ([
            'Paid orders today' => $summary['orders_today'],
            'Paid orders (window)' => $summary['orders_in_window'],
            'Revenue (window)' => money($summary['revenue_in_window']),
            'New buyers' => $summary['new_buyers'],
            'New producers' => $summary['new_producers'],
        ] as $label => $value)
            <div class="rounded-2xl border border-line bg-surface px-4 py-3">
                <p class="text-xs text-ink-muted">{{ $label }}</p>
                <p class="mt-1 font-mono text-lg font-semibold">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    {{-- Source filter --}}
    <section class="mt-10">
        <h2 class="text-sm font-medium text-ink-muted">Journeys by source</h2>
        <div class="mt-3 flex flex-wrap gap-2 text-sm">
            <a href="{{ route('ops.dashboard') }}" @class(['chip min-h-9 px-3', 'border-signal/50 bg-signal/15 text-signal' => ! $source, 'border-line-strong bg-raised text-ink-soft hover:text-ink' => $source])>all</a>
            @foreach ($summary['journeys_by_source'] as $name => $count)
                <a href="{{ route('ops.dashboard', ['source' => $name]) }}" @class(['chip min-h-9 px-3', 'border-signal/50 bg-signal/15 text-signal' => $source === $name, 'border-line-strong bg-raised text-ink-soft hover:text-ink' => $source !== $name])>{{ $name }} · {{ $count }}</a>
            @endforeach
        </div>
    </section>

    {{-- Journeys --}}
    <section class="mt-8">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="text-lg font-semibold">Recent journeys</h2>
            <p class="flex flex-wrap items-center gap-3 text-xs text-ink-muted">
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-ok"></span>progress</span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-warn"></span>interruption</span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-fail"></span>abandoned</span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-line-strong"></span>step</span>
            </p>
        </div>

        <div class="mt-4 space-y-2">
            @forelse ($journeys as $j)
                @php($steps = $j['sequence'] === '' ? [] : explode(' > ', $j['sequence']))
                <a href="{{ route('ops.journeys.show', $j['uuid']) }}" class="group block rounded-2xl border border-line bg-surface p-4 transition hover:border-signal/40 hover:bg-raised sm:p-5">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        <span class="font-mono text-sm font-semibold text-signal">{{ $j['code'] }}</span>
                        <span class="{{ $outcomeChip($j['outcome']) }}">{{ $j['outcome'] }}</span>
                        <span class="chip-muted">{{ $j['source'] }}</span>
                        <span class="min-w-0 truncate text-sm text-ink-soft">{{ $j['user'] ?? 'anonymous' }}@if ($j['role']) <span class="text-ink-muted">· {{ $j['role'] }}</span>@endif</span>
                        <span class="ml-auto font-mono text-xs text-ink-muted">{{ $j['started_at']->format('m-d H:i') }}</span>
                    </div>

                    <div class="mt-3 flex h-1.5 gap-0.5 overflow-hidden rounded-full" aria-hidden="true">
                        @foreach ($steps as $step)
                            <span class="{{ $segment[$eventTone($step)] }} flex-1"></span>
                        @endforeach
                    </div>

                    <p class="mt-3 font-mono text-[11px] leading-relaxed break-words text-ink-muted group-hover:text-ink-soft">
                        @foreach ($steps as $step)<span @class(['text-ok' => $eventTone($step) === 'ok', 'text-warn' => $eventTone($step) === 'warn', 'text-fail' => $eventTone($step) === 'fail'])>{{ $step }}</span>@unless ($loop->last)<span class="text-ink-muted/60"> › </span>@endunless @endforeach
                    </p>
                </a>
            @empty
                <p class="card text-center text-ink-muted">No journeys recorded.</p>
            @endforelse
        </div>
    </section>

    {{-- Orders --}}
    <section class="mt-10">
        <h2 class="text-lg font-semibold">Recent orders</h2>
        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
            @forelse ($orders as $order)
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-line bg-surface px-4 py-3">
                    <div class="min-w-0">
                        <p class="font-mono text-sm">{{ $order->reference }} <span class="text-ink-muted">· {{ $order->created_at->format('m-d H:i') }}</span></p>
                        <p class="mt-0.5 truncate text-xs text-ink-muted">{{ $order->event->slug }} · {{ $order->user->email }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="font-mono text-sm">{{ money($order->total) }} <span class="text-ink-muted">× {{ $order->quantity }}</span></p>
                        <span class="{{ $order->status->value === 'paid' ? 'chip-ok' : 'chip-muted' }} mt-1">{{ $order->status->value }}</span>
                    </div>
                </div>
            @empty
                <p class="card text-center text-ink-muted sm:col-span-2">No orders yet.</p>
            @endforelse
        </div>
    </section>
@endsection
