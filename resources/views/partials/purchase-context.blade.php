{{-- Summary of the ticket the visitor was buying, shown on the auth pages while a purchase is in progress. --}}
@php($quantity = \App\Support\PurchaseIntent::quantityFor(request()->session(), $purchase))
<div class="overflow-hidden rounded-2xl border border-signal/25 bg-signal/[0.06]">
    <div class="flex items-center gap-3 border-b border-signal/15 px-5 py-3">
        <span class="relative inline-flex size-2 text-signal"><span class="pulse-ring size-2 rounded-full bg-signal"></span></span>
        <p class="text-xs font-medium tracking-wide text-signal uppercase">Checkout in progress</p>
    </div>
    <div class="flex items-center justify-between gap-4 px-5 py-4">
        <div class="min-w-0">
            <p class="truncate font-semibold">{{ $purchase->title }}</p>
            <p class="mt-0.5 text-sm text-ink-muted">{{ $quantity }} {{ \Illuminate\Support\Str::plural('ticket', $quantity) }} · {{ $purchase->starts_at->format('M j, Y') }}</p>
        </div>
        <p class="shrink-0 font-semibold">{{ money($purchase->price * $quantity) }}</p>
    </div>
    <p class="border-t border-signal/15 px-5 py-3 text-xs text-ink-soft">Your checkout continues right after this step.</p>
</div>
