@extends('layouts.app')

@section('title', 'New event')

@section('content')
    <form method="POST" action="{{ route('producer.events.store') }}" class="card mx-auto max-w-lg space-y-4">
        @csrf
        <h1 class="text-xl font-bold">New event</h1>
        <div>
            <label class="label" for="title">Title</label>
            <input id="title" name="title" value="{{ old('title') }}" class="field" required>
            @error('title') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label" for="description">Description</label>
            <textarea id="description" name="description" rows="4" class="field" required>{{ old('description') }}</textarea>
            @error('description') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label" for="venue">Venue</label>
            <input id="venue" name="venue" value="{{ old('venue') }}" class="field">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="label" for="price">Price (USD)</label>
                <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price') }}" class="field" required>
                @error('price') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="starts_at">Starts at</label>
                <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" class="field" required>
                @error('starts_at') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <button type="submit" class="btn-primary w-full">Save draft</button>
    </form>
@endsection
