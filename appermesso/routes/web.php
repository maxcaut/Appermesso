<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfileController;
use App\Services\SupabaseProfileService;
use App\Services\SupabaseSession;
use Illuminate\Support\Facades\Route;

Route::get('/', function (SupabaseSession $session, SupabaseProfileService $profiles) {
    $auth = $session->current();
    $profile = [];

    if ($auth !== null) {
        try {
            $profile = $profiles->find(
                (string) $auth['user']['id'],
                (string) $auth['access_token'],
            ) ?? [];
        } catch (Throwable) {
            // A temporary profile failure must not disable the guest-capable form.
        }
    }

    return view('welcome', [
        'currentUser' => $auth['user'] ?? null,
        'profile' => $profile,
    ]);
})->name('home');

Route::view('/privacy/consenso-rifiutato', 'privacy-refused')->name('privacy.refused');

Route::post('/genera-pdf', [PdfController::class, '__invoke'])->name('pdf.generate');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.attempt');
    Route::get('/registrazione', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registrazione', [AuthController::class, 'register'])
        ->middleware('throttle:6,1')
        ->name('register.store');
});

Route::get('/password/dimenticata', [PasswordController::class, 'request'])->name('password.request');
Route::post('/password/email', [PasswordController::class, 'email'])
    ->middleware('throttle:6,1')
    ->name('password.email');
Route::post('/password/sessione-recupero', [PasswordController::class, 'session'])
    ->middleware('throttle:6,1')
    ->name('password.session');
Route::get('/password/reimposta', [PasswordController::class, 'reset'])->name('password.reset');
Route::post('/password/reimposta', [PasswordController::class, 'update'])
    ->middleware(['supabase.auth', 'throttle:6,1'])
    ->name('password.update');

Route::middleware('supabase.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profilo', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profilo', [ProfileController::class, 'update'])->name('profile.update');
});
