@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="eyebrow">Checkout</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">Review and pay</h1>
            </div>
            <span class="chip-warn">Simulated checkout · no real payment</span>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[1fr_400px]">
            {{-- Order summary --}}
            <section class="card p-0" aria-labelledby="summary-heading">
                <div class="border-b border-line px-6 py-5">
                    <h2 id="summary-heading" class="text-sm font-medium text-ink-muted">Order summary</h2>
                    <p class="mt-2 text-xl font-semibold">{{ $event->title }}</p>
                    <p class="mt-1 text-sm text-ink-muted">{{ $event->starts_at->format('D, M j Y · H:i') }} · {{ $event->venue }}</p>
                </div>
                <dl class="space-y-3 px-6 py-5 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Ticket</dt><dd>General admission</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Price</dt><dd class="font-mono">{{ $event->formattedPrice() }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Quantity</dt><dd class="font-mono">× {{ $quantity }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Subtotal</dt><dd class="font-mono">{{ money($total) }}</dd></div>
                    <div class="flex justify-between gap-4 border-t border-line pt-4 text-base font-semibold"><dt>Total</dt><dd class="font-mono">{{ money($total) }}</dd></div>
                </dl>
                <div class="border-t border-line px-6 py-4">
                    <p class="text-xs text-ink-muted">Customer</p>
                    <p class="mt-1 text-sm font-medium">{{ auth()->user()?->name }} <span class="text-ink-muted">· {{ auth()->user()?->email }}</span></p>
                </div>
            </section>

            {{-- Payment --}}
            <form method="POST" action="{{ route('checkout.store', $event) }}" class="card h-fit space-y-5 sm:p-7">
                @csrf
                <input type="hidden" name="quantity" value="{{ $quantity }}">
                <div>
                    <h2 class="text-lg font-semibold">Payment</h2>
                    <p class="mt-1 text-sm text-ink-muted">Payments in this environment are simulated. No card data is collected.</p>
                </div>
                <div>
                    <label class="label" for="cardholder">Name on ticket</label>
                    <input id="cardholder" name="cardholder" class="field" value="{{ old('cardholder', auth()->user()->name) }}" autocomplete="name" required>
                    @error('cardholder') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-line bg-canvas px-4 py-3">
                    <span class="grid grid-cols-1 size-9 place-items-center rounded-lg border border-line-strong bg-raised font-mono text-[10px] text-ink-soft">SIM</span>
                    <div>
                        <p class="text-sm font-medium">Demo payment</p>
                        <p class="text-xs text-ink-muted">Local simulated gateway · always approves</p>
                    </div>
                </div>
                <button type="submit" class="btn-primary btn-lg w-full">Pay {{ money($total) }}</button>
            </form>
        </div>
    </div>
@endsection
