@extends('layouts.app')

@section('title', 'Organizer dashboard')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $profile->display_name }}</h1>
            <p class="text-sm text-slate-500">Organizer dashboard</p>
        </div>
        <a href="{{ route('producer.events.create') }}" class="btn-primary">New event</a>
    </div>

    <div class="card p-0">
        <table class="w-full text-sm">
            <thead class="border-b bg-slate-50 text-left text-slate-500">
                <tr><th class="px-4 py-2">Event</th><th class="px-4 py-2">Date</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Paid orders</th><th class="px-4 py-2">Revenue</th><th class="px-4 py-2">Share link</th></tr>
            </thead>
            <tbody>
                @forelse ($events as $event)
                    <tr class="border-b last:border-0 align-top">
                        <td class="px-4 py-2 font-medium">{{ $event->title }}</td>
                        <td class="px-4 py-2">{{ $event->starts_at->format('M j, Y') }}</td>
                        <td class="px-4 py-2">{{ $event->status->value }}</td>
                        <td class="px-4 py-2">{{ $event->paid_orders_count }}</td>
                        <td class="px-4 py-2">{{ money((int) $event->revenue) }}</td>
                        <td class="px-4 py-2">
                            @if ($event->isPublished())
                                <code class="break-all text-xs">{{ $event->shareUrl() }}</code>
                            @else
                                <form method="POST" action="{{ route('producer.events.publish', $event) }}">
                                    @csrf
                                    <button class="btn-secondary px-2 py-1 text-xs">Publish</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No events yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
