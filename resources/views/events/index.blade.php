@extends('layouts.journeyops')

@section('title', 'From broken user journeys to verified fixes')

@php
    $repo = config('journeyops.repository');
    $shareLink = route('events.show', config('journeyops.demo_event')).'?ref=share';
    $shared = [
        ['event_page_viewed', 'guest', 'source=event_share'],
        ['buy_clicked', 'guest', 'quantity=1'],
        ['checkout_started', 'guest', null],
        ['auth_required', 'guest', 'expected=/checkout/…'],
        ['signup_started', 'guest', 'context=checkout'],
    ];
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="relative isolate overflow-hidden">
        <div class="bg-grid absolute inset-0 -z-10" aria-hidden="true"></div>
        <div class="glow-signal absolute inset-x-0 top-0 -z-10 h-[520px]" aria-hidden="true"></div>

        <div class="mx-auto grid grid-cols-1 max-w-6xl items-center gap-12 px-4 pt-14 pb-16 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:gap-10 lg:pt-20 lg:pb-24">
            <div>
                <p class="chip-info mb-6">
                    <span class="size-1.5 rounded-full bg-signal"></span>
                    IBM Bob 2.0 Hackathon · workflow prototype
                </p>
                <h1 class="text-[2.5rem] leading-[1.05] font-semibold tracking-tight text-balance sm:text-6xl">
                    The test suite was green.
                    <span class="block bg-gradient-to-r from-signal via-sky-300 to-electric bg-clip-text text-transparent">The user journey wasn't.</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink-soft">
                    JourneyOps gives IBM Bob evidence of what actually happened to users. With that context, Bob can discover, fix and verify failures that green test suites miss.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $shareLink }}" class="btn-primary btn-lg">
                        Launch live demo
                        <svg class="size-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                    </a>
                    <a href="#before-after" class="btn-secondary btn-lg">View before → after</a>
                </div>
                <p class="mt-4 text-sm text-ink-muted">
                    New here? Follow the <a href="{{ route('demo') }}" class="text-signal underline-offset-4 hover:underline">5-step guided demo</a>. It takes about two minutes.
                </p>

                <dl class="mt-10 grid grid-cols-3 gap-px overflow-hidden rounded-2xl border border-line bg-line text-center">
                    <div class="bg-surface px-3 py-4">
                        <dt class="text-xs text-ink-muted">Tests</dt>
                        <dd class="mt-1 font-mono text-sm sm:text-base"><span class="text-ink-muted">45</span> <span class="text-ink-muted">→</span> <span class="font-semibold text-ink">48</span></dd>
                    </div>
                    <div class="bg-surface px-3 py-4">
                        <dt class="text-xs text-ink-muted">Journey</dt>
                        <dd class="mt-1 font-mono text-[11px] sm:text-sm"><span class="text-fail">ABANDONED</span><span class="block text-ink-muted sm:inline"> → </span><span class="font-semibold text-ok">COMPLETED</span></dd>
                    </div>
                    <div class="bg-surface px-3 py-4">
                        <dt class="text-xs text-ink-muted">IBM Bob</dt>
                        <dd class="mt-1 text-[11px] leading-snug font-medium text-ink-soft sm:text-sm">Discover · Plan<br class="sm:hidden"> · Fix · Verify</dd>
                    </div>
                </dl>
            </div>

            {{-- Journey trace visual --}}
            <div class="relative" data-trace data-trace-autoplay="after">
                <div class="absolute -inset-4 -z-10 rounded-[2rem] bg-gradient-to-b from-signal/10 to-transparent blur-2xl" aria-hidden="true"></div>
                <div class="panel overflow-hidden shadow-2xl shadow-black/60">
                    <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="flex gap-1.5" aria-hidden="true"><span class="size-2.5 rounded-full bg-line-strong"></span><span class="size-2.5 rounded-full bg-line-strong"></span><span class="size-2.5 rounded-full bg-line-strong"></span></span>
                            <span class="font-mono text-xs text-ink-muted">journey trace · share link → checkout signup</span>
                        </div>
                    </div>

                    <div class="flex gap-1 border-b border-line p-2" role="tablist" aria-label="Journey state">
                        <button type="button" role="tab" data-trace-tab="before" aria-selected="true" aria-controls="trace-before" id="tab-before"
                                class="flex-1 rounded-lg px-3 py-2 text-left text-sm font-medium text-ink-muted transition aria-selected:bg-fail/10 aria-selected:text-fail">
                            <span class="block font-mono text-[10px] tracking-widest uppercase opacity-80">baseline-pre-bob</span>
                            Before IBM Bob
                        </button>
                        <button type="button" role="tab" data-trace-tab="after" aria-selected="false" aria-controls="trace-after" id="tab-after" tabindex="-1"
                                class="flex-1 rounded-lg px-3 py-2 text-left text-sm font-medium text-ink-muted transition aria-selected:bg-ok/10 aria-selected:text-ok">
                            <span class="block font-mono text-[10px] tracking-widest uppercase opacity-80">bob-final-verified</span>
                            After IBM Bob
                        </button>
                    </div>

                    <ol class="space-y-2.5 px-5 pt-5 font-mono text-[13px]" aria-label="Shared journey steps">
                        @foreach ($shared as [$name, $role, $meta])
                            <li class="trace-node flex items-start gap-3" data-trace-step style="animation-delay: {{ $loop->index * 70 }}ms">
                                <span @class(['dot', 'dot-warn' => $name === 'auth_required'])></span>
                                <span class="min-w-0 pt-0.5">
                                    <span class="text-ink">{{ $name }}</span>
                                    <span class="text-ink-muted"> · {{ $role }}</span>
                                    @if ($meta)<span class="block truncate text-xs text-ink-muted">{{ $meta }}</span>@endif
                                </span>
                            </li>
                        @endforeach
                    </ol>

                    <div id="trace-before" role="tabpanel" aria-labelledby="tab-before" data-trace-panel="before" class="px-5 pt-2.5 pb-5">
                        <ol class="space-y-2.5 font-mono text-[13px]">
                            <li class="trace-node flex items-start gap-3" data-trace-step>
                                <span class="dot dot-fail"></span>
                                <span class="pt-0.5"><span class="text-ink">signup_completed</span> <span class="text-ink-muted">·</span> <span class="text-fail">producer</span>
                                    <span class="block text-xs text-fail/90">target_route=/producer/onboarding</span></span>
                            </li>
                            <li class="trace-node flex items-start gap-3" data-trace-step style="animation-delay: 90ms">
                                <span class="dot dot-fail"></span>
                                <span class="pt-0.5 text-ink">producer_onboarding_viewed</span>
                            </li>
                            <li class="trace-node flex items-start gap-3" data-trace-step style="animation-delay: 180ms">
                                <span class="dot dot-fail"></span>
                                <span class="pt-0.5 text-ink">journey_abandoned</span>
                            </li>
                        </ol>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-fail/25 bg-fail/5 px-4 py-3" data-trace-step style="animation-delay: 260ms">
                            <span class="text-sm text-ink-soft">No order · no payment</span>
                            <span class="chip-fail">ABANDONED</span>
                        </div>
                    </div>

                    <div id="trace-after" role="tabpanel" aria-labelledby="tab-after" data-trace-panel="after" class="px-5 pt-2.5 pb-5" hidden>
                        <ol class="space-y-2.5 font-mono text-[13px]">
                            <li class="trace-node flex items-start gap-3" data-trace-step>
                                <span class="dot dot-ok"></span>
                                <span class="pt-0.5"><span class="text-ink">signup_completed</span> <span class="text-ink-muted">·</span> <span class="text-ok">buyer</span>
                                    <span class="block text-xs text-ok/90">target_route=/checkout/ai-builders-night-2026</span></span>
                            </li>
                            @foreach (['checkout_resumed', 'order_created', 'payment_started', 'payment_completed'] as $step)
                                <li class="trace-node flex items-start gap-3" data-trace-step style="animation-delay: {{ ($loop->index + 1) * 90 }}ms">
                                    <span class="dot dot-ok"></span>
                                    <span class="pt-0.5 text-ink">{{ $step }}</span>
                                </li>
                            @endforeach
                        </ol>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-ok/25 bg-ok/5 px-4 py-3" data-trace-step style="animation-delay: 470ms">
                            <span class="text-sm text-ink-soft">Order created · payment completed</span>
                            <span class="chip-ok">COMPLETED</span>
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-ink-muted">Recorded by the Ticket Lab's journey telemetry. Values are from the repository's replay evidence.</p>
            </div>
        </div>
    </section>

    {{-- ================= PROBLEM ================= --}}
    <section class="border-t border-line">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6" data-reveal>
            <div class="max-w-3xl">
                <p class="eyebrow">The problem</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Start from what the user experienced.</h2>
                <p class="mt-4 text-lg leading-relaxed text-ink-soft">
                    Most coding agents start when a developer already knows what needs fixing. Many real failures never raise an error. They surface only as a user who ended up in the wrong place and left.
                </p>
            </div>
            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach ([
                    ['Wrong redirect', 'The user lands somewhere reasonable, just not where they were going.'],
                    ['Unexpected role', 'A default quietly decides who the user is.'],
                    ['Silent funnel loss', 'No exception, no failing test. Just fewer completed journeys.'],
                ] as [$title, $body])
                    <div class="card">
                        <h3 class="font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink-muted">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
            <p class="mt-8 max-w-3xl text-ink-soft">
                <span class="font-semibold text-ink">JourneyOps starts one step earlier.</span>
                User-journey telemetry, application state and source code become the context IBM Bob investigates. Bob gets evidence, not a bug ticket.
            </p>
        </div>
    </section>

    {{-- ================= BEFORE / AFTER ================= --}}
    <section id="before-after" class="scroll-mt-20 border-t border-line bg-surface/40">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="max-w-3xl" data-reveal>
                <p class="eyebrow">Before → after</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">From ABANDONED to COMPLETED.</h2>
                <p class="mt-4 text-lg text-ink-soft">
                    Same visitor, same shared link, same “create account at checkout” path. The only difference is IBM Bob's fix.
                </p>
            </div>

            @php
                $rows = [
                    ['signup_completed.user_role', 'producer', 'buyer'],
                    ['signup_completed.target_route', '/producer/onboarding', '/checkout/ai-builders-night-2026'],
                    ['next journey event', 'producer_onboarding_viewed', 'checkout_resumed'],
                    ['order', '—', 'PAID'],
                    ['payment', '—', 'payment_completed'],
                    ['test suite', '45 passed', '48 passed · 191 assertions'],
                ];
            @endphp

            <div class="mt-10 grid grid-cols-1 gap-4 lg:grid-cols-2" data-reveal>
                <article class="relative overflow-hidden rounded-2xl border border-fail/25 bg-surface">
                    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-fail/60 to-transparent" aria-hidden="true"></div>
                    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-6 py-5">
                        <div>
                            <p class="font-mono text-xs text-ink-muted">tag baseline-pre-bob</p>
                            <h3 class="mt-1 text-lg font-semibold">Synthetic baseline before IBM Bob</h3>
                        </div>
                        <span class="chip-fail text-sm">ABANDONED</span>
                    </header>
                    <dl class="divide-y divide-line">
                        @foreach ($rows as [$label, $before])
                            <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-[13rem_1fr] sm:gap-4">
                                <dt class="font-mono text-xs text-ink-muted sm:pt-0.5">{{ $label }}</dt>
                                <dd class="font-mono text-sm break-all {{ $before === '—' ? 'text-ink-muted' : 'text-ink' }}">{{ $before }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </article>

                <article class="relative overflow-hidden rounded-2xl border border-ok/30 bg-surface shadow-[0_0_60px_-20px_rgb(52_211_153/0.35)]">
                    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-ok/70 to-transparent" aria-hidden="true"></div>
                    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-6 py-5">
                        <div>
                            <p class="font-mono text-xs text-ink-muted">tag bob-final-verified</p>
                            <h3 class="mt-1 text-lg font-semibold">Verified after IBM Bob</h3>
                        </div>
                        <span class="chip-ok text-sm">COMPLETED</span>
                    </header>
                    <dl class="divide-y divide-line">
                        @foreach ($rows as [$label, $before, $after])
                            <div class="grid grid-cols-1 gap-1 px-6 py-3.5 sm:grid-cols-[13rem_1fr] sm:gap-4">
                                <dt class="font-mono text-xs text-ink-muted sm:pt-0.5">{{ $label }}</dt>
                                <dd class="font-mono text-sm break-all text-ok">{{ $after }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </article>
            </div>

            <p class="mt-6 text-sm text-ink-muted" data-reveal>
                Explicit producer signup is unchanged in both states (<span class="font-mono">account_type=producer → /producer/onboarding</span>).
                Reproduce both sides with <span class="kbd">php artisan demo:replay-checkout</span> at each tag.
            </p>
        </div>
    </section>

    {{-- ================= IBM BOB WORKFLOW ================= --}}
    <section id="workflow" class="scroll-mt-20 border-t border-line">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="max-w-3xl" data-reveal>
                <p class="eyebrow">IBM Bob workflow</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Four tasks, each checkable by the next.</h2>
                <p class="mt-4 text-lg text-ink-soft">IBM Bob found the failure without being told where the bug was. Later tasks relied only on the exported record of the earlier ones.</p>
            </div>

            @php
                $stages = [
                    ['01', 'Discover', 'Ask mode', 'Receives operational evidence and is told not to assume a bug. Uses subagents to read telemetry, then traces one anomalous journey into the code.', 'anomaly discovered', 'info', 'journeyops_task01_operational_analysis.md'],
                    ['02', 'Plan', 'Plan mode', 'Confirms the root cause, compares four remediation strategies, and defines regression tests and acceptance criteria.', '4 options compared', 'info', 'journeyops_task02_remediation_plan.md'],
                    ['03', 'Fix', 'Agent mode', 'Re-validates the plan, implements a config-independent two-layer fix, adds 3 regression tests and replays the journey.', '48 tests · 191 assertions', 'ok', 'journeyops_task03_implementation.md'],
                    ['04', 'Verify', 'Agent mode · no edits', 'Independently re-audits source, tests, an HTTP replay and telemetry, without changing any source code.', 'COMPLETED · clean git', 'ok', 'journeyops_task04_final_verification.md'],
                ];
            @endphp

            <div class="relative mt-12" data-reveal>
            <div class="absolute top-[2.35rem] right-[12%] left-[12%] hidden h-px bg-gradient-to-r from-signal/0 via-signal/50 to-ok/0 lg:block" aria-hidden="true"></div>
            <ol class="relative grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($stages as [$num, $title, $mode, $body, $metric, $tone, $file])
                    <li class="relative flex flex-col rounded-2xl border border-line bg-surface p-5 transition hover:border-line-strong">
                        <div class="flex items-center justify-between">
                            <span @class([
                                'relative z-10 grid size-10 place-items-center rounded-xl border bg-canvas font-mono text-sm font-semibold',
                                'border-signal/40 text-signal' => $tone === 'info',
                                'border-ok/40 text-ok' => $tone === 'ok',
                            ])>{{ $num }}</span>
                            <span class="font-mono text-[11px] text-ink-muted">Task {{ $num }}</span>
                        </div>
                        <h3 class="mt-5 text-xl font-semibold tracking-tight">{{ $title }}</h3>
                        <p class="mt-1 text-xs font-medium text-ink-muted">{{ $mode }}</p>
                        <p class="mt-3 flex-1 text-sm leading-relaxed text-ink-soft">{{ $body }}</p>
                        <div class="mt-5 flex items-center justify-between gap-2">
                            <span class="{{ $tone === 'ok' ? 'chip-ok' : 'chip-info' }}">{{ $metric }}</span>
                            <a href="{{ $repo }}/blob/main/bob_sessions/{{ $file }}" class="text-xs font-medium text-ink-muted hover:text-signal" rel="noopener" aria-label="Task {{ $num }} evidence on GitHub">Evidence ↗</a>
                        </div>
                    </li>
                @endforeach
            </ol>
            </div>
        </div>
    </section>

    {{-- ================= ARCHITECTURE ================= --}}
    <section id="architecture" class="scroll-mt-20 border-t border-line bg-surface/40">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="max-w-3xl" data-reveal>
                <p class="eyebrow">Architecture</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Operational evidence → verified fix.</h2>
                <p class="mt-4 text-lg text-ink-soft">The Ticket Lab is only the controlled environment. JourneyOps is the loop around it.</p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-4 lg:grid-cols-[1fr_1.25fr_1fr]" data-reveal>
                {{-- Evidence --}}
                <div class="flex flex-col gap-3">
                    <p class="font-mono text-xs tracking-widest text-ink-muted uppercase">Evidence</p>
                    @foreach ([
                        ['User journey', 'real HTTP flow · synthetic visitors'],
                        ['Ticket Lab', 'Laravel 13 · checkout · auth · roles'],
                        ['Journey recorder', 'role · route · target · outcome per step'],
                        ['Telemetry + app state', 'SQLite · journey.jsonl · ops:* commands'],
                    ] as [$t, $s])
                        <div class="rounded-xl border border-line bg-surface px-4 py-3">
                            <p class="text-sm font-semibold">{{ $t }}</p>
                            <p class="mt-0.5 font-mono text-xs text-ink-muted">{{ $s }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Bob --}}
                <div class="relative flex flex-col justify-center rounded-2xl border border-signal/30 bg-gradient-to-b from-signal/[0.07] to-electric/[0.04] p-6 shadow-[0_0_80px_-30px_rgb(56_189_248/0.5)]">
                    <p class="font-mono text-xs tracking-widest text-signal uppercase">IBM Bob</p>
                    <p class="mt-2 text-sm text-ink-soft">Reads telemetry, code, tests and docs in the workspace. Reasons from evidence to a root cause.</p>
                    <div class="mt-6 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4">
                        @foreach (['Discover', 'Plan', 'Fix', 'Verify'] as $step)
                            <div class="rounded-lg border border-line-strong bg-canvas/70 px-3 py-2.5 text-center">
                                <p class="font-mono text-[10px] text-ink-muted">0{{ $loop->iteration }}</p>
                                <p class="text-sm font-semibold">{{ $step }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-6 font-mono text-xs text-ink-muted">Ask → Plan → Agent → Agent (no edits)</p>
                </div>

                {{-- Verification --}}
                <div class="flex flex-col gap-3">
                    <p class="font-mono text-xs tracking-widest text-ink-muted uppercase">Verification</p>
                    @foreach ([
                        ['Regression tests', 'php artisan test · 48 passed', 'text-ink'],
                        ['Journey replay', 'demo:replay-checkout · exit 0', 'text-ink'],
                        ['Telemetry check', 'buyer · checkout_resumed · paid', 'text-ink'],
                        ['Verified outcome', 'tag bob-final-verified', 'text-ok'],
                    ] as [$t, $s, $c])
                        <div class="rounded-xl border {{ $loop->last ? 'border-ok/30 bg-ok/5' : 'border-line bg-surface' }} px-4 py-3">
                            <p class="text-sm font-semibold {{ $c }}">{{ $t }}</p>
                            <p class="mt-0.5 font-mono text-xs text-ink-muted">{{ $s }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <p class="mt-6 text-sm text-ink-muted" data-reveal>
                Detailed diagrams: <a href="{{ $repo }}/blob/main/docs/ARCHITECTURE.md" class="text-signal underline-offset-4 hover:underline" rel="noopener">docs/ARCHITECTURE.md</a>
            </p>
        </div>
    </section>

    {{-- ================= EVIDENCE ================= --}}
    <section id="evidence" class="scroll-mt-20 border-t border-line">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-[1fr_1.3fr]">
                <div data-reveal>
                    <p class="eyebrow">Evidence</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Every claim is in the repository.</h2>
                    <p class="mt-4 text-ink-soft">
                        The Ticket Lab and its seeded incident were prepared before IBM Bob began. Git tags mark both ends, and Bob's four task exports are committed unedited.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <a class="chip-muted hover:border-signal/40 hover:text-ink" href="{{ $repo }}/tree/baseline-pre-bob" rel="noopener">tag baseline-pre-bob</a>
                        <a class="chip-muted hover:border-signal/40 hover:text-ink" href="{{ $repo }}/commit/8c2cd31" rel="noopener">fix 8c2cd31</a>
                        <a class="chip-muted hover:border-signal/40 hover:text-ink" href="{{ $repo }}/tree/bob-final-verified" rel="noopener">tag bob-final-verified</a>
                    </div>
                    <a href="{{ $repo }}" class="btn-secondary mt-8" rel="noopener">
                        <svg class="size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
                        View the repository
                    </a>
                </div>

                <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2" data-reveal>
                    @foreach ([
                        ['Task 01 · Investigation', 'journeyops_task01_operational_analysis'],
                        ['Task 02 · Remediation plan', 'journeyops_task02_remediation_plan'],
                        ['Task 03 · Implementation', 'journeyops_task03_implementation'],
                        ['Task 04 · Final verification', 'journeyops_task04_final_verification'],
                    ] as [$label, $file])
                        <li class="card flex flex-col gap-3 p-5">
                            <p class="font-semibold">{{ $label }}</p>
                            <div class="flex flex-wrap gap-2 text-sm">
                                <a href="{{ $repo }}/blob/main/bob_sessions/{{ $file }}.md" class="btn-secondary min-h-10 px-3 text-xs" rel="noopener">Task export</a>
                                <a href="{{ $repo }}/blob/main/bob_sessions/{{ $file }}_summary.png" class="btn-ghost min-h-10 px-3 text-xs" rel="noopener">Session screenshot</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ================= LIVE LAB ================= --}}
    <section id="lab" class="scroll-mt-20 border-t border-line bg-surface/40">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                <div data-reveal>
                    <p class="eyebrow">Live demo</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Walk the fixed journey yourself.</h2>
                    <p class="mt-4 text-ink-soft">Open the shared event link as a new visitor, create an account at checkout and pay with the simulated gateway. The journey your visit records is the evidence.</p>
                    <ol class="mt-8 space-y-3">
                        @foreach ([
                            'Open the shared event link',
                            'Click “Buy ticket”, then “Create an account”',
                            'Register with any address and return to checkout as a buyer',
                            'Pay (simulated) and see your journey marked COMPLETED',
                        ] as $step)
                            <li class="flex gap-3">
                                <span class="grid grid-cols-1 size-7 shrink-0 place-items-center rounded-full border border-line-strong font-mono text-xs text-ink-soft">{{ $loop->iteration }}</span>
                                <span class="pt-0.5 text-ink-soft">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ $shareLink }}" class="btn-primary btn-lg">Launch live ticket journey</a>
                        <a href="{{ route('demo') }}" class="btn-secondary btn-lg">Open the guided demo</a>
                    </div>
                </div>

                <div data-reveal>
                    <p class="font-mono text-xs tracking-widest text-ink-muted uppercase">Ticket Lab catalog · synthetic events</p>
                    <div class="mt-3 grid grid-cols-1 gap-3">
                        @forelse ($events as $event)
                            <a href="{{ route('events.show', $event) }}" class="group flex items-center justify-between gap-4 rounded-2xl border border-line bg-surface p-5 transition hover:border-signal/40 hover:bg-raised">
                                <span class="min-w-0">
                                    <span class="block font-mono text-xs text-signal">{{ $event->starts_at->format('D, M j Y · H:i') }}</span>
                                    <span class="mt-1 block truncate font-semibold text-ink">{{ $event->title }}</span>
                                    <span class="mt-0.5 block truncate text-sm text-ink-muted">{{ $event->producer->producerProfile?->display_name ?? $event->producer->name }} · {{ $event->venue }}</span>
                                </span>
                                <span class="shrink-0 text-right">
                                    <span class="block font-semibold">{{ $event->formattedPrice() }}</span>
                                    <span class="text-xs text-ink-muted transition group-hover:text-signal">View →</span>
                                </span>
                            </a>
                        @empty
                            <p class="card text-ink-muted">No events published yet. Run <span class="kbd">php artisan demo:reset --force</span>.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= HONESTY ================= --}}
    <section class="border-t border-line">
        <div class="mx-auto grid grid-cols-1 max-w-6xl gap-4 px-4 py-12 sm:grid-cols-3 sm:px-6">
            @foreach ([
                ['Synthetic data', 'Every account uses example.test. No real users or customer data.'],
                ['Simulated payment', 'A local gateway approves every order. No card data is collected.'],
                ['Controlled incident', 'The defect was seeded before IBM Bob started (tag baseline-pre-bob). It is not a production outage.'],
            ] as [$t, $b])
                <div>
                    <p class="text-sm font-semibold">{{ $t }}</p>
                    <p class="mt-1 text-sm leading-relaxed text-ink-muted">{{ $b }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
