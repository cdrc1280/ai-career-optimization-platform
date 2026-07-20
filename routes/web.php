<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    // Password reset
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update');

    // Social login redirects and callbacks (google, github, microsoft)
    Route::get('/auth/redirect/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/callback/{provider}', [SocialAuthController::class, 'callback'])->name('social.callback');

    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.create');
});

Route::middleware('auth')->group(function () {
    // Email verification
    Route::get('/email/verify', [\App\Http\Controllers\Auth\VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\VerificationController::class, 'resend'])->name('verification.send');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Return a personal access token for the currently-authenticated user.
    // This is intended for local/dev convenience only — the token is also
    // stored in the session on login/register. The frontend will call this
    // with credentials to obtain a Bearer token automatically.
    Route::get('/me/token', function (Request $request) {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token = session('api_token');
        if (!$token) {
            $token = $user->createToken('web-session-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        return response()->json(['token' => $token]);
    })->name('me.token');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
