@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Checkout</h1>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="card">
            <h2 class="font-semibold">{{ $event->title }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $event->starts_at->format('D, M j Y · H:i') }} · {{ $event->venue }}</p>
            <dl class="mt-4 space-y-1 text-sm">
                <div class="flex justify-between"><dt>Price</dt><dd>{{ $event->formattedPrice() }}</dd></div>
                <div class="flex justify-between"><dt>Quantity</dt><dd>{{ $quantity }}</dd></div>
                <div class="flex justify-between border-t pt-2 font-semibold"><dt>Total</dt><dd>{{ money($total) }}</dd></div>
            </dl>
        </div>

        <form method="POST" action="{{ route('checkout.store', $event) }}" class="card space-y-4">
            @csrf
            <input type="hidden" name="quantity" value="{{ $quantity }}">
            <p class="text-sm text-slate-500">Payments in this environment are simulated. No card data is collected.</p>
            <div>
                <label class="label" for="cardholder">Name on ticket</label>
                <input id="cardholder" name="cardholder" class="field" value="{{ old('cardholder', auth()->user()->name) }}" required>
                @error('cardholder') <p class="error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary w-full">Pay {{ money($total) }}</button>
        </form>
    </div>
@endsection
