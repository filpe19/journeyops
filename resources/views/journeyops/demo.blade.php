@extends('layouts.journeyops')

@section('title', 'Guided demo')

@php
    $repo = config('journeyops.repository');
    $shareLink = route('events.show', config('journeyops.demo_event')).'?ref=share';
@endphp

@section('content')
    <section class="relative isolate overflow-hidden border-b border-line">
        <div class="bg-grid absolute inset-0 -z-10" aria-hidden="true"></div>
        <div class="mx-auto max-w-3xl px-4 pt-14 pb-12 sm:px-6">
            <p class="eyebrow">Guided demo · about 2 minutes</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">See the journey IBM Bob fixed.</h1>
            <p class="mt-4 text-lg text-ink-soft">
                Five steps. You'll walk the same path that used to fail, then inspect the telemetry your own visit produces.
            </p>
            <div class="mt-6 flex flex-wrap gap-2">
                <span class="chip-muted">synthetic data</span>
                <span class="chip-muted">simulated payment</span>
                <span class="chip-muted">controlled incident</span>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <ol class="space-y-4">
            {{-- STEP 1 --}}
            <li class="card relative">
                <div class="flex gap-4">
                    <span class="grid grid-cols-1 size-10 shrink-0 place-items-center rounded-xl border border-fail/40 bg-canvas font-mono text-sm font-semibold text-fail">1</span>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold">Understand the baseline failure</h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                            At <span class="font-mono text-ink">baseline-pre-bob</span>, a new visitor from a shared event link who created an account at checkout was made a <span class="font-mono text-fail">producer</span>. They were sent to organizer onboarding and never paid. All 45 tests passed.
                        </p>
                        <div class="mt-4 rounded-xl border border-line bg-canvas p-4 font-mono text-xs leading-relaxed text-ink-muted [overflow-wrap:anywhere]">
                            signup_started <span class="text-ink-soft">context=checkout</span><br>
                            signup_completed <span class="text-fail">user_role=producer target_route=/producer/onboarding</span><br>
                            producer_onboarding_viewed → journey_abandoned <span class="text-fail">· ABANDONED</span>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('home') }}#before-after" class="btn-secondary">View before → after</a>
                            <a href="{{ $repo }}/blob/main/bob_sessions/journeyops_task01_operational_analysis.md" class="btn-ghost" rel="noopener">How Bob found it ↗</a>
                        </div>
                    </div>
                </div>
            </li>

            {{-- STEP 2 --}}
            <li class="card relative border-signal/30">
                <div class="flex gap-4">
                    <span class="grid grid-cols-1 size-10 shrink-0 place-items-center rounded-xl border border-signal/40 bg-canvas font-mono text-sm font-semibold text-signal">2</span>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold">Launch the live, fixed journey</h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                            Open <span class="font-semibold text-ink">AI Builders Night 2026</span> through its shared link, the same entry point as the original incident. If you are signed in, sign out first so you arrive as a new visitor.
                        </p>
                        @auth
                            <p class="mt-3 rounded-lg border border-warn/30 bg-warn/10 px-3 py-2 text-sm text-warn [overflow-wrap:anywhere]">You're currently signed in as {{ auth()->user()->email }}. Sign out to replay the new-visitor journey.</p>
                        @endauth
                        <div class="mt-4">
                            <a href="{{ $shareLink }}" class="btn-primary">Launch live ticket journey →</a>
                        </div>
                    </div>
                </div>
            </li>

            {{-- STEP 3 --}}
            <li class="card relative">
                <div class="flex gap-4">
                    <span class="grid grid-cols-1 size-10 shrink-0 place-items-center rounded-xl border border-line-strong bg-canvas font-mono text-sm font-semibold text-ink-soft">3</span>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold">Complete registration and checkout</h2>
                        <ol class="mt-3 space-y-2 text-sm text-ink-soft">
                            <li><span class="text-ink-muted">a.</span> Click <span class="kbd">Buy ticket</span>. Checkout asks you to sign in.</li>
                            <li><span class="text-ink-muted">b.</span> Choose <span class="kbd">Create an account</span>. Use any name and a new address such as <span class="font-mono text-ink">you+1@example.test</span>.</li>
                            <li><span class="text-ink-muted">c.</span> You return to checkout <span class="text-ok">as a buyer</span>, not to organizer onboarding.</li>
                            <li><span class="text-ink-muted">d.</span> Press <span class="kbd">Pay</span>. The gateway is simulated and no card is requested.</li>
                        </ol>
                    </div>
                </div>
            </li>

            {{-- STEP 4 --}}
            <li class="card relative scroll-mt-24" id="inspect">
                <div class="flex gap-4">
                    <span class="grid grid-cols-1 size-10 shrink-0 place-items-center rounded-xl border border-line-strong bg-canvas font-mono text-sm font-semibold text-ink-soft">4</span>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold">Inspect the resulting journey</h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                            The order confirmation page reads the journey your visit just recorded from the telemetry store: the outcome, the checkout and payment events, and the role your signup created. Expand <span class="text-ink">Full event sequence</span> to see every step.
                        </p>
                        <p class="mt-3 text-sm leading-relaxed text-ink-muted">
                            The full operations console shows every visitor's journeys, so it isn't public in this demo. It's shown in the project video and can be run locally (see the demo guide).
                        </p>
                        @auth
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('account') }}" class="btn-secondary">My tickets</a>
                            </div>
                        @endauth
                    </div>
                </div>
            </li>

            {{-- STEP 5 --}}
            <li class="card relative border-ok/30">
                <div class="flex gap-4">
                    <span class="grid grid-cols-1 size-10 shrink-0 place-items-center rounded-xl border border-ok/40 bg-canvas font-mono text-sm font-semibold text-ok">5</span>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold">Compare with the baseline</h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                            Your journey should read <span class="font-mono text-ok">signup_completed · buyer → checkout_resumed → order_created → payment_completed</span>. That's the opposite of the baseline. The code difference is one commit by IBM Bob.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ $repo }}/commit/8c2cd31" class="btn-secondary" rel="noopener">View the fix ↗</a>
                            <a href="{{ $repo }}/compare/baseline-pre-bob...bob-final-verified" class="btn-ghost" rel="noopener">Compare tags ↗</a>
                            <a href="{{ $repo }}/blob/main/bob_sessions/journeyops_task04_final_verification.md" class="btn-ghost" rel="noopener">Bob's verification ↗</a>
                        </div>
                    </div>
                </div>
            </li>
        </ol>

        <p class="mt-10 text-sm text-ink-muted">
            Want to reproduce the broken baseline locally? See
            <a href="{{ $repo }}/blob/main/docs/DEMO_GUIDE.md" class="text-signal underline-offset-4 hover:underline" rel="noopener">docs/DEMO_GUIDE.md</a>.
            The public demo only runs the verified state.
        </p>
    </section>
@endsection
