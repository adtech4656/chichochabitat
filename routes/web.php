<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{slug}', [PropertyController::class, 'show'])->name('properties.show');

// Language and currency preferences persist in one-year cookies.
Route::post('/locale/{locale}', LocaleController::class)
    ->middleware('throttle:preferences')
    ->name('locale.update');
Route::post('/currency/{code}', CurrencyController::class)
    ->middleware('throttle:preferences')
    ->name('currency.update');

// Admin-authored content pages (footer, trust, legal).
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');

Route::middleware('guest')->group(function (): void {
    Route::post('/register', [AuthController::class, 'store'])
        ->middleware('throttle:register')
        ->name('register');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/account', AccountController::class)->name('account');

Route::middleware('auth')->group(function (): void {
    // Profile & Security updates
    Route::put('/account/profile', [AuthController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AuthController::class, 'updatePassword'])->name('account.password.update');

    // Saved properties / Favorites
    Route::post('/properties/{property}/save', [PropertyController::class, 'toggleSave'])->name('properties.save');

    // Chat and negotiations
    Route::get('/chat/conversations', [ChatController::class, 'index'])->name('chat.conversations.index');
    Route::post('/chat/conversations', [ChatController::class, 'store'])->name('chat.conversations.store');
    Route::get('/chat/conversations/{conversation}', [ChatController::class, 'show'])->name('chat.conversations.show');
    Route::post('/chat/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('chat.conversations.messages.store');
    Route::post('/chat/conversations/{conversation}/state', [ChatController::class, 'transitionState'])->name('chat.conversations.transition');
    Route::post('/chat/conversations/{conversation}/assign', [ChatController::class, 'assignAgent'])->name('chat.conversations.assign');
    Route::post('/chat/conversations/{conversation}/appointments', [ChatController::class, 'scheduleAppointment'])->name('chat.appointments.schedule');
    Route::post('/chat/conversations/{conversation}/appointments/{appointment}/verify', [ChatController::class, 'verifyAppointment'])->name('chat.appointments.verify');

    // Document checks
    Route::post('/properties/{property}/document-access', [ChatController::class, 'generateDocumentAccess'])->name('properties.document-access.generate');
    Route::post('/properties/{property}/document-access/verify', [ChatController::class, 'unlockDocuments'])->name('properties.document-access.verify');

    // Commercial offers
    Route::post('/offers', [OfferController::class, 'store'])
        ->middleware('throttle:offers')
        ->name('offers.store');
    Route::post('/offers/{offer}/accept', [OfferController::class, 'accept'])
        ->middleware('throttle:offers')
        ->name('offers.accept');
    Route::post('/offers/{offer}/reject', [OfferController::class, 'reject'])
        ->middleware('throttle:offers')
        ->name('offers.reject');
});

// Agent onboarding (React). The agent workspace itself lives at /agent (Filament).
Route::prefix('become-agent')->name('agent.')->group(function (): void {
    Route::get('/kyc', [AgentController::class, 'kyc'])->name('kyc');
    Route::post('/kyc', [AgentController::class, 'submitKyc'])->name('kyc.submit');
    Route::get('/capture', [AgentController::class, 'create'])->name('listings.create');
    Route::post('/capture', [AgentController::class, 'storeListing'])->name('listings.store');
});

// Alias for seamless navigation
Route::redirect('/agent/kyc', '/become-agent/kyc');
