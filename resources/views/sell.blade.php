@extends('layouts.app')

@section('title', 'Sell tickets')

@section('content')
    <div class="mx-auto max-w-2xl text-center">
        <h1 class="text-3xl font-bold">Sell tickets for your events</h1>
        <p class="mt-3 text-slate-600">Create an event, publish it and share one link. Buyers check out in seconds and you follow every sale in real time.</p>
        <a href="{{ route('register', ['type' => 'producer']) }}" class="btn-primary mt-6">Create an organizer account</a>
    </div>
@endsection
