@extends('layouts.app')

@section('title', 'Set up your organizer profile')

@section('content')
    <div class="mx-auto max-w-md">
        <form method="POST" action="{{ route('producer.onboarding.store') }}" class="card space-y-5 sm:p-8">
            @csrf
            <p class="eyebrow">Organizer onboarding · step 1 of 1</p>
            <h1 class="text-2xl font-semibold tracking-tight">Set up your organizer profile</h1>
            <p class="text-sm text-ink-muted">This is the name buyers will see on your event pages.</p>
            <div>
                <label class="label" for="display_name">Organizer name</label>
                <input id="display_name" name="display_name" value="{{ old('display_name', $user->name) }}" class="field" required>
                @error('display_name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary btn-lg w-full">Continue to organizer dashboard</button>
        </form>
    </div>
@endsection
