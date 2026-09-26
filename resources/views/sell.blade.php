@extends('layouts.app')

@section('title', 'Sell tickets')

@section('content')
    <div class="mx-auto max-w-2xl py-10 text-center">
        <p class="eyebrow">For organizers</p>
        <h1 class="mt-3 text-4xl font-semibold tracking-tight text-balance sm:text-5xl">Sell tickets for your events</h1>
        <p class="mt-4 text-lg text-ink-soft">Create an event, publish it and share one link. Buyers check out in seconds and you follow every sale in real time.</p>
        <a href="{{ route('register', ['type' => 'producer']) }}" class="btn-primary btn-lg mt-8">Create an organizer account</a>
        <p class="mt-6 text-sm text-ink-muted">Explicit organizer signup is the legitimate producer path. It still leads to organizer onboarding.</p>
    </div>
@endsection
