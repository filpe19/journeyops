@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
    <div class="mx-auto max-w-md">
        @if ($purchase)
            <div class="mb-4 rounded-md border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
                Sign in to finish buying your ticket for <strong>{{ $purchase->title }}</strong>.
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="card space-y-4">
            @csrf
            <h1 class="text-xl font-bold">Sign in</h1>
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="field" required autofocus>
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input id="password" type="password" name="password" class="field" required>
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button type="submit" class="btn-primary w-full">Sign in</button>
            <p class="text-center text-sm text-slate-500">New here? <a href="{{ route('register') }}" class="font-semibold text-indigo-600">Create an account</a></p>
        </form>
    </div>
@endsection
