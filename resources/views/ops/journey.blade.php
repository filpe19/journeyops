@extends('layouts.app')

@section('title', $row['code'])

@php
    // Visual tone per journey event: ok = purchase progress, warn = interruption, fail = loss, info = neutral step.
    $eventTone = static fn (string $name): string => match (true) {
        in_array($name, ['checkout_resumed', 'order_created', 'payment_started', 'payment_completed', 'login_completed', 'signup_completed', 'producer_onboarding_completed'], true) => 'ok',
        $name === 'auth_required' => 'warn',
        $name === 'journey_abandoned' => 'fail',
        default => 'info',
    };
    $outcomeChip = match ($row['outcome']) {
        'completed' => 'chip-ok',
        'abandoned' => 'chip-fail',
        'active' => 'chip-info',
        default => 'chip-muted',
    };
    $hidden = ['journey_uuid'];
@endphp

@section('content')
    <a href="{{ route('ops.dashboard') }}" class="inline-flex min-h-10 items-center gap-1.5 text-sm text-ink-muted hover:text-ink">&larr; Operations</a>

    <div class="mt-2 flex flex-wrap items-center gap-3">
        <h1 class="font-mono text-2xl font-semibold tracking-tight sm:text-3xl">{{ $row['code'] }}</h1>
        <span class="{{ $outcomeChip }} text-sm">{{ strtoupper($row['outcome']) }}</span>
    </div>
    <p class="mt-1 font-mono text-xs break-all text-ink-muted">{{ $journey->uuid }}</p>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[320px_1fr]">
        {{-- Journey facts --}}
        <aside class="lg:sticky lg:top-6 lg:self-start">
            <dl class="card grid grid-cols-2 gap-x-4 gap-y-4 p-5 text-sm lg:grid-cols-1">
                <div><dt class="text-xs text-ink-muted">Source</dt><dd class="mt-0.5 font-mono">{{ $journey->source }}</dd></div>
                <div><dt class="text-xs text-ink-muted">Event</dt><dd class="mt-0.5 font-mono break-all">{{ $journey->event?->slug ?? '—' }}</dd></div>
                <div class="col-span-2 lg:col-span-1"><dt class="text-xs text-ink-muted">Entry route</dt><dd class="mt-0.5 font-mono text-xs break-all">{{ $journey->entry_route }}</dd></div>
                <div class="col-span-2 lg:col-span-1"><dt class="text-xs text-ink-muted">User</dt><dd class="mt-0.5 break-all">{{ $journey->user?->email ?? 'anonymous' }}</dd></div>
                <div><dt class="text-xs text-ink-muted">Current role</dt><dd class="mt-0.5 font-mono">{{ $journey->user?->role?->value ?? '—' }}</dd></div>
                <div><dt class="text-xs text-ink-muted">Producer profile</dt><dd class="mt-0.5">{{ $journey->user?->producerProfile ? 'yes' : 'no' }}</dd></div>
                <div><dt class="text-xs text-ink-muted">Events</dt><dd class="mt-0.5 font-mono">{{ $row['events_count'] }}</dd></div>
                <div><dt class="text-xs text-ink-muted">Outcome</dt><dd class="mt-0.5 font-mono font-semibold">{{ $row['outcome'] }}</dd></div>
                <div class="col-span-2 lg:col-span-1"><dt class="text-xs text-ink-muted">Started / ended</dt><dd class="mt-0.5 font-mono text-xs">{{ $journey->started_at->format('Y-m-d H:i:s') }}<br>{{ $journey->ended_at?->format('Y-m-d H:i:s') ?? 'open' }}</dd></div>
            </dl>
        </aside>

        {{-- Timeline --}}
        <section class="card p-5 sm:p-6" aria-labelledby="timeline-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="timeline-heading" class="text-lg font-semibold">Timeline</h2>
                <p class="text-xs text-ink-muted">Expand a step to see its recorded metadata.</p>
            </div>

            <ol class="mt-6">
                @foreach ($journey->events as $e)
                    @php
                        $tone = $eventTone($e->event_name);
                        $meta = \Illuminate\Support\Arr::except($e->metadata ?? [], $hidden);
                        $role = $meta['user_role'] ?? null;
                        $target = $meta['target_route'] ?? ($meta['expected_destination'] ?? null);
                    @endphp
                    <li class="trace-node flex gap-4 pb-5 last:pb-0">
                        <span class="dot dot-{{ $tone }} mt-2.5 {{ $loop->last && $row['outcome'] === 'completed' ? 'pulse-ring text-ok' : '' }}"></span>
                        <details class="group min-w-0 flex-1 rounded-xl border border-transparent transition open:border-line open:bg-canvas/60">
                            <summary class="flex min-h-11 cursor-pointer list-none flex-wrap items-center gap-x-3 gap-y-1 rounded-xl px-3 py-2 hover:bg-raised [&::-webkit-details-marker]:hidden">
                                <span @class(['font-mono text-sm font-semibold', 'text-ok' => $tone === 'ok', 'text-warn' => $tone === 'warn', 'text-fail' => $tone === 'fail', 'text-ink' => $tone === 'info'])>{{ $e->event_name }}</span>
                                @if ($role)
                                    <span class="chip-muted py-0.5">{{ $role }}</span>
                                @endif
                                <span class="font-mono text-xs text-ink-muted">{{ $e->route }}</span>
                                <span class="ml-auto font-mono text-xs text-ink-muted">{{ $e->occurred_at->format('H:i:s') }}</span>
                                @if ($target)
                                    <span class="basis-full font-mono text-xs text-ink-muted">→ {{ $target }}</span>
                                @endif
                            </summary>
                            <pre class="overflow-x-auto px-3 pt-1 pb-3 font-mono text-[11px] leading-relaxed text-ink-soft">{{ json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
@endsection
