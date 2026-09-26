@extends('layouts.app')

@section('title', 'Organizer dashboard')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Organizer dashboard</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $profile->display_name }}</h1>
        </div>
        <a href="{{ route('producer.events.create') }}" class="btn-primary">New event</a>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-3">
        @forelse ($events as $event)
            <div class="rounded-2xl border border-line bg-surface p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ $event->title }}</p>
                        <p class="mt-0.5 text-sm text-ink-muted">{{ $event->starts_at->format('M j, Y') }}</p>
                    </div>
                    <span class="{{ $event->isPublished() ? 'chip-ok' : 'chip-muted' }}">{{ $event->status->value }}</span>
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                    <div><dt class="text-xs text-ink-muted">Paid orders</dt><dd class="mt-0.5 font-mono">{{ $event->paid_orders_count }}</dd></div>
                    <div><dt class="text-xs text-ink-muted">Revenue</dt><dd class="mt-0.5 font-mono">{{ money((int) $event->revenue) }}</dd></div>
                    <div class="col-span-2 sm:col-span-1">
                        <dt class="text-xs text-ink-muted">Share link</dt>
                        <dd class="mt-0.5">
                            @if ($event->isPublished())
                                <code class="font-mono text-xs break-all text-ink-soft">{{ $event->shareUrl() }}</code>
                            @else
                                <form method="POST" action="{{ route('producer.events.publish', $event) }}">
                                    @csrf
                                    <button class="btn-secondary min-h-9 px-3 py-1 text-xs">Publish</button>
                                </form>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        @empty
            <p class="card text-center text-ink-muted">No events yet.</p>
        @endforelse
    </div>
@endsection
