<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'github', 'microsoft']), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'github', 'microsoft']), 404);

        // Socialite may throw — let Laravel report the error and show a friendly page.
        $socialUser = Socialite::driver($provider)->user();

        $user = User::where('oauth_provider', $provider)
            ->where('oauth_id', $socialUser->getId())
            ->first();

        if (!$user && $socialUser->getEmail()) {
            // If a user exists with this email, attach the oauth info.
            $user = User::where('email', $socialUser->getEmail())->first();
        }

        if (!$user) {
            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'User',
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(40)),
                'oauth_provider' => $provider,
                'oauth_id' => $socialUser->getId(),
                'email_verified_at' => now(),
            ]);
        } else {
            if (!$user->oauth_provider || !$user->oauth_id) {
                $user->update(['oauth_provider' => $provider, 'oauth_id' => $socialUser->getId()]);
            }
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Create and store an API token for SPA convenience.
        $token = $user->createToken('web-social-token')->plainTextToken;
        $request->session()->put('api_token', $token);

        return redirect()->route('dashboard');
    }
}
