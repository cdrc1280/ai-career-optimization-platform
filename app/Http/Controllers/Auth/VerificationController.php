<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class VerificationController extends Controller
{
    public function notice()
    {
        return view('auth.verify-email');
    }

    public function verify(Request $request, $id, $hash)
    {
        // Use the built-in email verification request if available.
        $user = $request->user();
        if (! $user || (string) $user->getKey() !== (string) $id) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return Redirect::route('dashboard');
        }

        $user->markEmailAsVerified();

        return Redirect::route('dashboard');
    }

    public function resend(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
