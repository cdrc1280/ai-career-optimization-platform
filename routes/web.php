<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Horizon\Horizon;

// Root redirect
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ─── Guest-only routes ────────────────────────────────────────────────────────
// These now serve the Vue SPA and let Vue Router handle the page rendering.
// The old Blade auth views are no longer used.
Route::middleware('guest')->group(function () {
    Route::get('/login',    fn() => view('layouts.spa'))->name('login');
    Route::get('/register', fn() => view('layouts.spa'))->name('register');

    // Password reset (kept server-side for email-link flows)
    Route::get('/forgot-password',       [\App\Http\Controllers\Auth\PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password',      [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}',[\App\Http\Controllers\Auth\PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password',       [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update');

    // Social OAuth
    Route::get('/auth/redirect/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/callback/{provider}', [SocialAuthController::class, 'callback'])->name('social.callback');

    // Legacy form-based POST endpoints (kept for Blade fallback)
    Route::post('/login',    [LoginController::class, 'login'])->name('login.attempt');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.create');
});

// ─── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Email verification
    Route::get('/email/verify',                 [\App\Http\Controllers\Auth\VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}',     [\App\Http\Controllers\Auth\VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\VerificationController::class, 'resend'])->name('verification.send');

    // Dashboard entry point
    Route::get('/dashboard', fn() => view('layouts.spa'))->name('dashboard');

    // Token endpoint — Vue SPA calls this on mount to get a Bearer token
    Route::get('/me/token', function (Request $request) {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $token = session('api_token') ?? $user->createToken('web-session-token')->plainTextToken;
        $request->session()->put('api_token', $token);
        return response()->json(['token' => $token, 'user' => $user]);
    })->name('me.token');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // SPA catch-all — every authenticated path is handled by Vue Router
    Route::get('/{any}', fn() => view('layouts.spa'))
        ->where('any', '^(?!api|sanctum|email|auth|logout|me|forgot-password|reset-password).*')
        ->name('spa');
});

Horizon::auth(function (Request $request) {
    return $request->user()?->isAdmin() ?? false;
});
