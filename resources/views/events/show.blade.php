@extends('layouts.app')

@section('title', $event->title)

@section('content')
    <div class="grid gap-8 md:grid-cols-3">
        <div class="md:col-span-2">
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">{{ $event->starts_at->format('l, F j, Y · H:i') }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ $event->title }}</h1>
            <p class="mt-2 text-slate-500">Presented by {{ $event->producer->producerProfile?->display_name ?? $event->producer->name }} · {{ $event->venue }}</p>
            <div class="mt-6 space-y-4 whitespace-pre-line leading-relaxed text-slate-700">{{ $event->description }}</div>
        </div>

        <aside class="card h-fit">
            <p class="text-sm text-slate-500">General admission</p>
            <p class="mt-1 text-2xl font-bold">{{ $event->formattedPrice() }}</p>

            <form method="POST" action="{{ route('checkout.buy', $event) }}" class="mt-4 space-y-3">
                @csrf
                <label class="label" for="quantity">Tickets</label>
                <select id="quantity" name="quantity" class="field">
                    @foreach (range(1, 6) as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </select>
                @error('quantity') <p class="error">{{ $message }}</p> @enderror
                <button type="submit" class="btn-primary w-full">Buy ticket</button>
            </form>
        </aside>
    </div>
@endsection
