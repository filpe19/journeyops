@extends('layouts.app')

@section('title', 'Order confirmed')

@section('content')
    <div class="card mx-auto max-w-lg text-center">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600">Purchase complete</p>
        <h1 class="mt-2 text-2xl font-bold">You're going to {{ $order->event->title }}</h1>
        <p class="mt-2 text-slate-500">Order {{ $order->reference }} · {{ $order->quantity }} ticket(s) · {{ money($order->total) }}</p>
        <p class="mt-1 text-sm text-slate-500">Status: {{ $order->status->value }}</p>
        <a href="{{ route('account') }}" class="btn-secondary mt-6">View my tickets</a>
    </div>
@endsection
