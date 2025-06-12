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

// Route per il webhook di Twilio
Route::post('/twilio/webhook', [App\Http\Controllers\TwilioController::class, 'handleIncomingMessage'])
    ->name('twilio.webhook')
    ->withoutMiddleware(['web', 'csrf']);

Route::post('/twilio/test', [App\Http\Controllers\TwilioController::class, 'testWebhook'])->name('twilio.test');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
