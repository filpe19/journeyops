@extends('layouts.app')

@section('title', $row['code'])

@section('content')
    <a href="{{ route('ops.dashboard') }}" class="text-sm text-indigo-600">&larr; Operations</a>
    <h1 class="mt-2 font-mono text-2xl font-bold">{{ $row['code'] }}</h1>
    <p class="font-mono text-xs text-slate-500">{{ $journey->uuid }}</p>

    <dl class="card mt-4 grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
        <div><dt class="text-xs text-slate-500">Source</dt><dd>{{ $journey->source }}</dd></div>
        <div><dt class="text-xs text-slate-500">Entry route</dt><dd class="font-mono text-xs">{{ $journey->entry_route }}</dd></div>
        <div><dt class="text-xs text-slate-500">Event</dt><dd>{{ $journey->event?->slug ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">Outcome</dt><dd class="font-semibold">{{ $row['outcome'] }}</dd></div>
        <div><dt class="text-xs text-slate-500">User</dt><dd>{{ $journey->user?->email ?? 'anonymous' }}</dd></div>
        <div><dt class="text-xs text-slate-500">Current role</dt><dd>{{ $journey->user?->role?->value ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">Producer profile</dt><dd>{{ $journey->user?->producerProfile ? 'yes' : 'no' }}</dd></div>
        <div><dt class="text-xs text-slate-500">Started / ended</dt><dd class="text-xs">{{ $journey->started_at->format('Y-m-d H:i:s') }}<br>{{ $journey->ended_at?->format('Y-m-d H:i:s') ?? 'open' }}</dd></div>
    </dl>

    <h2 class="mb-2 mt-8 font-semibold">Timeline</h2>
    <div class="card overflow-x-auto p-0">
        <table class="w-full text-xs">
            <thead class="border-b bg-slate-50 text-left text-slate-500">
                <tr><th class="px-3 py-2">Time</th><th class="px-3 py-2">Event</th><th class="px-3 py-2">Route</th><th class="px-3 py-2">Role</th><th class="px-3 py-2">Metadata</th></tr>
            </thead>
            <tbody>
                @foreach ($journey->events as $e)
                    <tr class="border-b align-top last:border-0">
                        <td class="whitespace-nowrap px-3 py-2 font-mono">{{ $e->occurred_at->format('H:i:s') }}</td>
                        <td class="px-3 py-2 font-semibold">{{ $e->event_name }}</td>
                        <td class="px-3 py-2 font-mono">{{ $e->route }}</td>
                        <td class="px-3 py-2">{{ $e->metadata['user_role'] ?? '—' }}</td>
                        <td class="px-3 py-2 font-mono text-[11px] text-slate-600">{{ json_encode(\Illuminate\Support\Arr::except($e->metadata ?? [], ['journey_uuid']), JSON_UNESCAPED_SLASHES) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
