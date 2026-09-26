@extends('layouts.app')

@section('title', $event->title)

@php
    $organizer = $event->producer->producerProfile?->display_name ?? $event->producer->name;
    $paragraphs = preg_split('/\n\s*\n/', trim($event->description));
@endphp

@section('content')
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_380px] lg:gap-12">
        <div class="min-w-0">
            {{-- Event hero --}}
            <div class="relative isolate overflow-hidden rounded-3xl border border-line bg-surface p-6 sm:p-10">
                <div class="absolute inset-0 -z-10 bg-[radial-gradient(700px_circle_at_85%_-10%,rgb(109_139_255/0.22),transparent_55%),radial-gradient(500px_circle_at_0%_110%,rgb(56_189_248/0.14),transparent_55%)]" aria-hidden="true"></div>
                <div class="bg-grid absolute inset-0 -z-10 opacity-60" aria-hidden="true"></div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="chip-info">JourneyOps demo scenario</span>
                    @if (request()->query('ref') === 'share')
                        <span class="chip-muted">via shared link</span>
                    @endif
                </div>

                <h1 class="mt-6 text-4xl leading-[1.05] font-semibold tracking-tight text-balance sm:text-5xl">{{ $event->title }}</h1>
                <p class="mt-3 text-ink-soft">Presented by <span class="font-medium text-ink">{{ $organizer }}</span></p>

                <dl class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-line bg-canvas/60 px-4 py-3">
                        <dt class="text-xs text-ink-muted">Date</dt>
                        <dd class="mt-1 font-semibold">{{ $event->starts_at->format('D, M j, Y') }}</dd>
                    </div>
                    <div class="rounded-2xl border border-line bg-canvas/60 px-4 py-3">
                        <dt class="text-xs text-ink-muted">Starts</dt>
                        <dd class="mt-1 font-semibold">{{ $event->starts_at->format('H:i') }}</dd>
                    </div>
                    <div class="rounded-2xl border border-line bg-canvas/60 px-4 py-3">
                        <dt class="text-xs text-ink-muted">Venue</dt>
                        <dd class="mt-1 font-semibold">{{ $event->venue ?: 'To be announced' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- About --}}
            <section class="mt-10">
                <h2 class="text-lg font-semibold">About this event</h2>
                <div class="mt-4 space-y-4 text-[17px] leading-relaxed text-ink-soft">
                    @foreach ($paragraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            </section>

            <section class="mt-10 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="card p-5">
                    <p class="text-xs text-ink-muted">Organizer</p>
                    <p class="mt-1 font-semibold">{{ $organizer }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-xs text-ink-muted">Capacity</p>
                    <p class="mt-1 font-semibold">{{ $event->capacity ? number_format($event->capacity).' guests' : 'Open' }}</p>
                </div>
            </section>
        </div>

        {{-- Ticket card --}}
        <aside class="lg:sticky lg:top-6 lg:self-start">
            <div class="overflow-hidden rounded-3xl border border-line-strong bg-surface shadow-2xl shadow-black/50">
                <div class="border-b border-line px-6 py-5">
                    <p class="text-sm text-ink-muted">General admission</p>
                    <p class="mt-1 flex items-baseline gap-2">
                        <span class="text-3xl font-semibold tracking-tight">{{ $event->formattedPrice() }}</span>
                        <span class="text-sm text-ink-muted">per ticket</span>
                    </p>
                </div>

                <form method="POST" action="{{ route('checkout.buy', $event) }}" class="space-y-5 px-6 py-6">
                    @csrf
                    <div>
                        <label class="label" for="quantity">Tickets</label>
                        <div class="relative">
                            <select id="quantity" name="quantity" class="field appearance-none pr-10">
                                @foreach (range(1, 6) as $n)
                                    <option value="{{ $n }}">{{ $n }} {{ \Illuminate\Support\Str::plural('ticket', $n) }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute top-1/2 right-3 mt-0.5 size-4 -translate-y-1/2 text-ink-muted" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 6 4 4 4-4"/></svg>
                        </div>
                        @error('quantity') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn-primary btn-lg w-full">Buy ticket</button>
                    <p class="text-center text-xs leading-relaxed text-ink-muted">
                        You'll sign in or create an account at checkout.<br>Simulated payment. No card required.
                    </p>
                </form>
            </div>

            <p class="mt-4 flex items-start gap-2 px-2 text-xs leading-relaxed text-ink-muted">
                <svg class="mt-0.5 size-3.5 shrink-0 text-signal" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="8" cy="8" r="6.5"/><path d="M8 7.5v3.5M8 5v.01"/></svg>
                Each step of this purchase is recorded as journey telemetry, the evidence JourneyOps gives IBM Bob.
            </p>
        </aside>
    </div>
@endsection
