<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tickets') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
        <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight">JourneyOps <span class="text-indigo-600">Tickets</span></a>
        <nav class="flex items-center gap-4 text-sm">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Events</a>
            @auth
                @if (auth()->user()->isProducer())
                    <a href="{{ route('producer.dashboard') }}" class="hover:text-indigo-600">Organizer</a>
                @endif
                @can('view-ops')
                    <a href="{{ route('ops.dashboard') }}" class="hover:text-indigo-600">Ops</a>
                @endcan
                <a href="{{ route('account') }}" class="hover:text-indigo-600">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-slate-800">Sign out</button>
                </form>
            @else
                <a href="{{ route('sell') }}" class="hover:text-indigo-600">Sell tickets</a>
                <a href="{{ route('login') }}" class="hover:text-indigo-600">Sign in</a>
            @endauth
        </nav>
    </div>
</header>

<main class="mx-auto max-w-5xl px-4 py-8">
    @if (session('status'))
        <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @yield('content')
</main>

<footer class="mx-auto max-w-5xl px-4 py-8 text-xs text-slate-400">
    {{ config('app.name') }} · synthetic demo data only
</footer>
</body>
</html>
