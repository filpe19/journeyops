<!DOCTYPE html>
<html lang="en" class="bg-canvas">
<head>
    @include('partials.head')
</head>
<body class="flex min-h-screen flex-col overflow-x-hidden bg-canvas font-sans text-ink antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-lg focus:bg-signal focus:px-4 focus:py-2 focus:text-slate-950">Skip to content</a>

{{-- Demo context bar: the Ticket Lab is the controlled environment used by the JourneyOps demo. --}}
<div class="border-b border-line bg-surface">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-2 text-xs sm:px-6">
        <a href="{{ route('home') }}" class="inline-flex min-h-8 items-center gap-2 font-medium text-ink-soft hover:text-ink">
            <span aria-hidden="true">&larr;</span>
            @include('partials.logo', ['class' => 'size-4'])
            <span>JourneyOps</span>
        </a>
        <p class="flex items-center gap-2 text-ink-muted">
            <span class="relative inline-flex size-2 text-signal"><span class="pulse-ring size-2 rounded-full bg-signal"></span></span>
            <span class="hidden sm:inline">Demo scenario · synthetic data · simulated payment</span>
            <span class="sm:hidden">Demo scenario</span>
            <a href="{{ route('demo') }}" class="ml-1 font-medium text-signal hover:underline underline-offset-4">Guide</a>
        </p>
    </div>
</div>

<header class="border-b border-line/70">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-3 px-4 sm:px-6">
        <a href="{{ route('events.show', config('journeyops.demo_event')) }}" class="flex items-center gap-2 font-semibold tracking-tight">
            <span class="grid grid-cols-1 size-8 place-items-center rounded-lg bg-gradient-to-br from-signal to-electric text-sm font-bold text-slate-950" aria-hidden="true">T</span>
            <span class="whitespace-nowrap">Ticket Lab</span>
        </a>
        <nav class="flex items-center gap-0.5 overflow-x-auto text-sm" aria-label="Ticket Lab">
            @guest
                <a href="{{ route('sell') }}" class="btn-ghost hidden min-h-10 px-3 sm:inline-flex">Sell tickets</a>
            @endguest
            @include('partials.account-nav')
        </nav>
    </div>
</header>

<main id="main" class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 sm:py-12">
    @if (session('status'))
        <div class="mb-6 rounded-xl border border-ok/30 bg-ok/10 px-4 py-3 text-sm text-ok" role="status">{{ session('status') }}</div>
    @endif

    @yield('content')
</main>

<footer class="border-t border-line">
    <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-xs text-ink-muted sm:flex-row sm:justify-between sm:px-6">
        <p>Ticket Lab: the controlled environment of the JourneyOps demo.</p>
        <p>All events, accounts and orders are synthetic. No real payments are processed.</p>
    </div>
</footer>
</body>
</html>
