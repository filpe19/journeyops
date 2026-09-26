@extends('layouts.app')

@section('title', 'Order confirmed')

@php
    $names = $journey ? $journey->events->pluck('event_name')->all() : [];
    $signup = $journey?->events->firstWhere('event_name', 'signup_completed');
    $checks = [
        'checkout_resumed' => 'Checkout resumed after authentication',
        'order_created' => 'Order created',
        'payment_completed' => 'Payment completed',
    ];
@endphp

@section('content')
    <div class="mx-auto grid grid-cols-1 max-w-5xl gap-6 lg:grid-cols-[1fr_1fr]">
        {{-- Confirmation --}}
        <section class="relative isolate overflow-hidden rounded-3xl border border-ok/30 bg-surface p-6 sm:p-8">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(500px_circle_at_20%_-10%,rgb(52_211_153/0.18),transparent_60%)]" aria-hidden="true"></div>
            <span class="grid grid-cols-1 size-12 place-items-center rounded-2xl border border-ok/40 bg-ok/10 text-ok">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
            </span>
            <p class="mt-6 text-sm font-medium tracking-wide text-ok uppercase">Purchase complete</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-balance">You're going to {{ $order->event->title }}</h1>
            <p class="mt-2 text-ink-soft">{{ $order->event->starts_at->format('l, F j, Y · H:i') }} · {{ $order->event->venue }}</p>

            <dl class="mt-8 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl border border-line bg-canvas/60 px-4 py-3">
                    <dt class="text-xs text-ink-muted">Order</dt>
                    <dd class="mt-1 font-mono">{{ $order->reference }}</dd>
                </div>
                <div class="rounded-xl border border-line bg-canvas/60 px-4 py-3">
                    <dt class="text-xs text-ink-muted">Status</dt>
                    <dd class="mt-1 font-mono text-ok uppercase">{{ $order->status->value }}</dd>
                </div>
                <div class="rounded-xl border border-line bg-canvas/60 px-4 py-3">
                    <dt class="text-xs text-ink-muted">Tickets</dt>
                    <dd class="mt-1">{{ $order->quantity }} × General admission</dd>
                </div>
                <div class="rounded-xl border border-line bg-canvas/60 px-4 py-3">
                    <dt class="text-xs text-ink-muted">Total</dt>
                    <dd class="mt-1 font-mono">{{ money($order->total) }}</dd>
                </div>
            </dl>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('account') }}" class="btn-secondary">View my tickets</a>
                <a href="{{ route('home') }}" class="btn-ghost">Return to JourneyOps</a>
            </div>
            <p class="mt-6 text-xs text-ink-muted">Simulated payment. No money was charged.</p>
        </section>

        {{-- JourneyOps evidence panel (demo only, read from recorded telemetry) --}}
        <section class="panel flex flex-col p-6 sm:p-8" aria-labelledby="journey-heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="eyebrow">JourneyOps · your journey</p>
                    <h2 id="journey-heading" class="mt-2 text-lg font-semibold">What the telemetry recorded</h2>
                </div>
                @if ($journey)
                    <span class="{{ $journey->outcome() === 'completed' ? 'chip-ok' : 'chip-muted' }} text-sm">{{ strtoupper($journey->outcome()) }}</span>
                @endif
            </div>

            @if ($journey)
                <p class="mt-2 font-mono text-xs text-ink-muted">{{ $journey->code() }} · source {{ $journey->source }}</p>

                <ul class="mt-6 space-y-2">
                    @foreach ($checks as $name => $label)
                        @php($seen = in_array($name, $names, true))
                        <li class="flex items-center justify-between gap-3 rounded-xl border px-4 py-2.5 {{ $seen ? 'border-ok/25 bg-ok/5' : 'border-line bg-canvas/50' }}">
                            <span class="min-w-0">
                                <span class="block font-mono text-sm {{ $seen ? 'text-ink' : 'text-ink-muted' }}">{{ $name }}</span>
                                <span class="block text-xs text-ink-muted">{{ $label }}</span>
                            </span>
                            <span class="{{ $seen ? 'chip-ok' : 'chip-muted' }}">{{ $seen ? 'recorded' : 'not in this journey' }}</span>
                        </li>
                    @endforeach
                </ul>

                @if ($signup)
                    <p class="mt-4 rounded-xl border border-line bg-canvas/50 px-4 py-3 font-mono text-xs text-ink-soft">
                        signup_completed · user_role=<span class="{{ ($signup->metadata['user_role'] ?? null) === 'buyer' ? 'text-ok' : 'text-fail' }}">{{ $signup->metadata['user_role'] ?? '?' }}</span>
                        · target_route={{ $signup->metadata['target_route'] ?? '?' }}
                    </p>
                @endif

                <details class="group mt-4">
                    <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between rounded-lg px-1 text-sm font-medium text-ink-soft hover:text-ink [&::-webkit-details-marker]:hidden">
                        Full event sequence ({{ count($names) }})
                        <svg class="size-4 transition group-open:rotate-180" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 6 4 4 4-4"/></svg>
                    </summary>
                    <ol class="mt-3 space-y-2 font-mono text-xs">
                        @foreach ($journey->events as $e)
                            <li class="trace-node flex items-start gap-3">
                                <span @class(['dot', 'dot-ok' => in_array($e->event_name, ['checkout_resumed', 'order_created', 'payment_started', 'payment_completed'], true), 'dot-warn' => $e->event_name === 'auth_required'])></span>
                                <span class="pt-1"><span class="text-ink">{{ $e->event_name }}</span> <span class="text-ink-muted">· {{ $e->metadata['user_role'] ?? '—' }}</span></span>
                            </li>
                        @endforeach
                    </ol>
                </details>
            @else
                <p class="mt-6 text-sm text-ink-muted">No journey telemetry is linked to this order.</p>
            @endif

            <div class="mt-auto flex flex-col gap-3 pt-8 sm:flex-row">
                @if ($journey && auth()->user()->can('view-ops'))
                    <a href="{{ route('ops.journeys.show', $journey->uuid) }}" class="btn-primary">View journey evidence</a>
                @else
                    <a href="{{ route('demo') }}#inspect" class="btn-primary">View journey evidence</a>
                @endif
                <a href="{{ route('home') }}#before-after" class="btn-secondary">Compare with baseline</a>
            </div>
        </section>
    </div>
@endsection
