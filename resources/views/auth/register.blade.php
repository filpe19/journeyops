@extends('layouts.app')

@section('title', 'Create account')

@section('content')
    <div class="mx-auto grid grid-cols-1 max-w-5xl items-start gap-8 lg:grid-cols-[1fr_420px] lg:gap-12">
        <div class="lg:pt-6">
            @if ($purchase)
                <p class="eyebrow">Almost there</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Create your account to continue checkout.</h1>
                <p class="mt-4 text-ink-soft">You're one step away from your ticket for <strong class="text-ink">{{ $purchase->title }}</strong>. We'll take you straight back to checkout.</p>
                <div class="mt-8">
                    @include('partials.purchase-context', ['purchase' => $purchase])
                </div>
            @else
                <p class="eyebrow">Ticket Lab account</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Buy tickets or sell your own.</h1>
                <p class="mt-4 text-ink-soft">Buyers keep their tickets in one place. Organizers publish events and share one link.</p>
            @endif
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="card space-y-5 sm:p-8">
            @csrf
            <div>
                <h2 class="text-xl font-semibold">Create your account</h2>
                <p class="mt-1 text-sm text-ink-muted">Nothing is emailed. This is a synthetic lab.</p>
                <p class="mt-3 flex gap-2 rounded-lg border border-line-strong bg-canvas px-3 py-2.5 text-sm text-ink-soft">
                    <svg class="mt-0.5 size-4 shrink-0 text-signal" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="8" cy="8" r="6.5"/><path d="M8 7.5v3.5M8 5v.01"/></svg>
                    <span>Synthetic demo: please use a test identity such as <span class="font-mono text-ink">name@example.test</span>, and don't enter personal information.</span>
                </p>
            </div>

            @unless ($purchase)
                <fieldset>
                    <legend class="label">I want to</legend>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                        <label class="flex min-h-11 cursor-pointer items-center gap-2.5 rounded-lg border border-line-strong bg-canvas px-3 py-2.5 transition has-checked:border-signal has-checked:bg-signal/10">
                            <input type="radio" name="account_type" value="buyer" class="accent-sky-400" @checked(old('account_type', $preselected) === 'buyer')> Buy tickets
                        </label>
                        <label class="flex min-h-11 cursor-pointer items-center gap-2.5 rounded-lg border border-line-strong bg-canvas px-3 py-2.5 transition has-checked:border-signal has-checked:bg-signal/10">
                            <input type="radio" name="account_type" value="producer" class="accent-sky-400" @checked(old('account_type', $preselected) === 'producer')> Sell tickets
                        </label>
                    </div>
                    @error('account_type') <p class="error">{{ $message }}</p> @enderror
                </fieldset>
            @endunless

            <div>
                <label class="label" for="name">Full name</label>
                <input id="name" name="name" value="{{ old('name') }}" class="field" autocomplete="name" required>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="field" autocomplete="email" placeholder="you@example.test" required>
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label" for="password">Password</label>
                    <input id="password" type="password" name="password" class="field" autocomplete="new-password" required>
                    @error('password') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="field" autocomplete="new-password" required>
                </div>
            </div>
            <button type="submit" class="btn-primary btn-lg w-full">Create account{{ $purchase ? ' and continue' : '' }}</button>
            <p class="text-center text-sm text-ink-muted">Already registered? <a href="{{ route('login') }}" class="font-semibold text-signal hover:underline underline-offset-4">Sign in</a></p>
        </form>
    </div>
@endsection
