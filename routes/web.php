<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Ops\DashboardController as OpsDashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Producer\DashboardController as ProducerDashboardController;
use App\Http\Controllers\Producer\EventController as ProducerEventController;
use App\Http\Controllers\Producer\OnboardingController;
use App\Http\Controllers\SellController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('health');

// Public catalog
Route::get('/', [EventController::class, 'index'])->name('home');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/sell', SellController::class)->name('sell');

// Purchase funnel
Route::post('/events/{event}/buy', [CheckoutController::class, 'buy'])->name('checkout.buy');
Route::get('/checkout/{event}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{event}', [CheckoutController::class, 'store'])->middleware('auth')->name('checkout.store');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Buyer area
Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'show'])->name('account');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Producer area
Route::middleware(['auth', 'role:producer'])->prefix('producer')->name('producer.')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    Route::middleware('producer.onboarded')->group(function () {
        Route::get('/', ProducerDashboardController::class)->name('dashboard');
        Route::get('/events/create', [ProducerEventController::class, 'create'])->name('events.create');
        Route::post('/events', [ProducerEventController::class, 'store'])->name('events.store');
        Route::post('/events/{event}/publish', [ProducerEventController::class, 'publish'])->name('events.publish');
    });
});

// Operations
Route::middleware(['auth', 'can:view-ops'])->prefix('ops')->name('ops.')->group(function () {
    Route::get('/', [OpsDashboardController::class, 'index'])->name('dashboard');
    Route::get('/journeys/{uuid}', [OpsDashboardController::class, 'show'])->name('journeys.show');
});
