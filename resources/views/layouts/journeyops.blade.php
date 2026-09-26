<!DOCTYPE html>
<html lang="en" class="bg-canvas">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen overflow-x-hidden bg-canvas font-sans text-ink antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-lg focus:bg-signal focus:px-4 focus:py-2 focus:text-slate-950">Skip to content</a>

<header class="sticky top-0 z-40 border-b border-line/70 bg-canvas/80 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-semibold tracking-tight text-ink">
            @include('partials.logo')
            <span class="text-[15px]">JourneyOps</span>
        </a>

        <nav class="hidden items-center gap-1 text-sm md:flex" aria-label="Primary">
            <a href="{{ route('demo') }}" class="btn-ghost min-h-10 px-3">Guided demo</a>
            <a href="{{ route('home') }}#architecture" class="btn-ghost min-h-10 px-3">Architecture</a>
            <a href="{{ route('home') }}#evidence" class="btn-ghost min-h-10 px-3">Evidence</a>
            <a href="{{ config('journeyops.repository') }}" class="btn-ghost min-h-10 px-3" rel="noopener">GitHub</a>
            <span class="mx-2 h-5 w-px bg-line-strong" aria-hidden="true"></span>
            @include('partials.account-nav')
            <a href="{{ route('events.show', config('journeyops.demo_event')) }}?ref=share" class="btn-primary ml-2 min-h-10">Launch demo</a>
        </nav>

        <details class="group relative md:hidden">
            <summary class="btn-secondary min-h-11 cursor-pointer list-none px-3 [&::-webkit-details-marker]:hidden" aria-label="Open menu">
                <svg class="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h14M3 10h14M3 14h14"/></svg>
                Menu
            </summary>
            <div class="absolute right-0 mt-2 flex w-64 flex-col gap-1 rounded-2xl border border-line-strong bg-surface p-2 shadow-2xl shadow-black/60">
                <a href="{{ route('events.show', config('journeyops.demo_event')) }}?ref=share" class="btn-primary mb-1 w-full">Launch demo</a>
                <a href="{{ route('demo') }}" class="btn-ghost justify-start">Guided demo</a>
                <a href="{{ route('home') }}#architecture" class="btn-ghost justify-start">Architecture</a>
                <a href="{{ route('home') }}#evidence" class="btn-ghost justify-start">Evidence</a>
                <a href="{{ config('journeyops.repository') }}" class="btn-ghost justify-start" rel="noopener">GitHub</a>
                <div class="my-1 hairline"></div>
                <div class="flex flex-col gap-1 [&_a]:justify-start [&_button]:w-full [&_button]:justify-start">
                    @include('partials.account-nav')
                </div>
            </div>
        </details>
    </div>
</header>

<main id="main">
    @yield('content')
</main>

<footer class="border-t border-line">
    <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-10 text-sm text-ink-muted sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div class="flex items-center gap-2.5">
            @include('partials.logo', ['class' => 'size-6'])
            <span>JourneyOps · IBM Bob 2.0 Hackathon project</span>
        </div>
        <p>Synthetic data · Simulated payments · Controlled incident · <a class="text-ink-soft underline-offset-4 hover:underline" href="{{ config('journeyops.repository') }}/blob/main/LICENSE" rel="noopener">MIT</a></p>
    </div>
</footer>
</body>
</html>
