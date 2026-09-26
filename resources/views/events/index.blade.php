@extends('layouts.app')

@section('title', 'Upcoming events')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Upcoming events</h1>

    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($events as $event)
            <a href="{{ route('events.show', $event) }}" class="card block hover:border-indigo-300">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $event->starts_at->format('D, M j Y · H:i') }}</p>
                <h2 class="mt-1 text-lg font-semibold">{{ $event->title }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $event->producer->producerProfile?->display_name ?? $event->producer->name }} · {{ $event->venue }}</p>
                <p class="mt-3 text-sm font-semibold">{{ $event->formattedPrice() }}</p>
            </a>
        @empty
            <p class="text-slate-500">No events published yet.</p>
        @endforelse
    </div>
@endsection
