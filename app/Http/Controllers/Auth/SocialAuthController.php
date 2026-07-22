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

        if (empty(config("services.{$provider}.client_id"))) {
            return redirect()->route('login', ['error' => 'oauth_config_missing']);
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('login', ['error' => 'oauth_config_missing']);
        } catch (\Throwable $e) {
            return redirect()->route('login', ['error' => 'oauth_failed']);
        }
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'github', 'microsoft']), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();

            $user = User::where('oauth_provider', $provider)
                ->where('oauth_id', $socialUser->getId())
                ->first();

            if (!$user && $socialUser->getEmail()) {
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

            $token = $user->createToken('web-social-token')->plainTextToken;
            $request->session()->put('api_token', $token);

            return redirect()->route('dashboard');
        } catch (\Throwable $e) {
            return redirect()->route('login', ['error' => 'oauth_failed']);
        }
    }
}
