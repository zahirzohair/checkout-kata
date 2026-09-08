<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');

Route::middleware('throttle:60,1')->group(function () {
    // ->block() serializes requests sharing a session, so two scans fired at
    // once can't race on reading/writing the same session-stored basket.
    // Must stay route-level: it locks the whole request, including the
    // moment the session actually saves — a lock only around the service
    // call would release before that save happens.
    Route::post('checkout/scan', [CheckoutController::class, 'scan'])->name('checkout.scan')->block();
    Route::post('checkout/reset', [CheckoutController::class, 'reset'])->name('checkout.reset')->block();
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
