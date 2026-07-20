<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $key = Str::lower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return Redirect::back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Too many login attempts. Please try again later.']);
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            return Redirect::back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'The provided credentials do not match our records.']);
        }

        RateLimiter::clear($key);

        $request->session()->regenerate();

        // Create a short-lived personal access token for API testing
        $user = Auth::user();
        if ($user) {
            $token = $user->createToken('web-login-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        return Redirect::intended(route('dashboard'));
    }

    public function loginApi(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $key = Str::lower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Too many login attempts. Try again later.'], 429);
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            return response()->json(['message' => 'The provided credentials do not match our records.'], 422);
        }

        RateLimiter::clear($key);

        $request->session()->regenerate();

        $user = Auth::user();
        $token = $user?->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('login');
    }
}
