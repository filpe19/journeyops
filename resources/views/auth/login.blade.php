@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
    <div class="mx-auto grid grid-cols-1 max-w-5xl items-start gap-8 lg:grid-cols-[1fr_420px] lg:gap-12">
        <div class="lg:pt-6">
            @if ($purchase)
                <p class="eyebrow">Checkout</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Sign in or create an account.</h1>
                <p class="mt-4 text-ink-soft">Sign in to finish buying your ticket for <strong class="text-ink">{{ $purchase->title }}</strong>.</p>
                <div class="mt-8">
                    @include('partials.purchase-context', ['purchase' => $purchase])
                </div>
                <div class="mt-6 rounded-2xl border border-line bg-surface p-5">
                    <p class="font-semibold">First time here?</p>
                    <p class="mt-1 text-sm text-ink-muted">Create an account in a few seconds. Your checkout is waiting for you.</p>
                    <a href="{{ route('register') }}" class="btn-primary mt-4 w-full sm:w-auto">Create an account</a>
                </div>
            @else
                <p class="eyebrow">Ticket Lab</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Welcome back.</h1>
                <p class="mt-4 text-ink-soft">Sign in to see your tickets or manage your events.</p>
                <div class="mt-8 rounded-2xl border border-line bg-surface p-5">
                    <p class="text-xs font-medium tracking-wide text-ink-muted uppercase">Synthetic demo operator</p>
                    <p class="mt-2 text-sm text-ink-soft">To inspect journeys in the ops console, sign in as</p>
                    <p class="mt-2 font-mono text-sm"><span class="text-ink">admin@example.test</span> <span class="text-ink-muted">/</span> <span class="text-ink">password</span></p>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="card space-y-5 sm:p-8">
            @csrf
            <h2 class="text-xl font-semibold">Sign in</h2>
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="field" autocomplete="email" required autofocus>
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input id="password" type="password" name="password" class="field" autocomplete="current-password" required>
            </div>
            <label class="flex min-h-11 items-center gap-2.5 text-sm text-ink-soft"><input type="checkbox" name="remember" value="1" class="size-4 accent-sky-400"> Remember me</label>
            <button type="submit" class="btn-primary btn-lg w-full">Sign in</button>
            <p class="text-center text-sm text-ink-muted">New here? <a href="{{ route('register') }}" class="font-semibold text-signal hover:underline underline-offset-4">Create an account</a></p>
        </form>
    </div>
@endsection
