@extends('layouts.app')

@section('title', 'Create account')

@section('content')
    <div class="mx-auto max-w-md">
        @if ($purchase)
            <div class="mb-4 rounded-md border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
                You're one step away from your ticket for <strong>{{ $purchase->title }}</strong>.
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="card space-y-4">
            @csrf
            <h1 class="text-xl font-bold">Create your account</h1>

            @unless ($purchase)
                <fieldset>
                    <legend class="label">I want to</legend>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                        <label class="flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2">
                            <input type="radio" name="account_type" value="buyer" @checked(old('account_type', $preselected) === 'buyer')> Buy tickets
                        </label>
                        <label class="flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2">
                            <input type="radio" name="account_type" value="producer" @checked(old('account_type', $preselected) === 'producer')> Sell tickets
                        </label>
                    </div>
                    @error('account_type') <p class="error">{{ $message }}</p> @enderror
                </fieldset>
            @endunless

            <div>
                <label class="label" for="name">Full name</label>
                <input id="name" name="name" value="{{ old('name') }}" class="field" required>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="field" required>
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input id="password" type="password" name="password" class="field" required>
                @error('password') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="field" required>
            </div>
            <button type="submit" class="btn-primary w-full">Create account</button>
            <p class="text-center text-sm text-slate-500">Already registered? <a href="{{ route('login') }}" class="font-semibold text-indigo-600">Sign in</a></p>
        </form>
    </div>
@endsection
