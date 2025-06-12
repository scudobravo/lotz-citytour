<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MonumentController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\TwilioController;

Route::get('/', [WelcomeController::class, 'index']);

Route::get('/points', [MonumentController::class, 'index']);

Route::get('/point/{id}', [MonumentController::class, 'show'])->name('point.show');

// Webhook per i messaggi Twilio
Route::post('/twilio/webhook', [TwilioController::class, 'handleIncomingMessage']);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
