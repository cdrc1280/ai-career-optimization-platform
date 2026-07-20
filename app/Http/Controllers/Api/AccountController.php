<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller {
    public function show(Request $request): JsonResponse {
        return response()->json($request->user()->load('profile'));
    }

    public function update(Request $request): JsonResponse {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
        ]);
        $user->update($validated);
        return response()->json($user);
    }

    public function changePassword(Request $request): JsonResponse {
        $user = $request->user();
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $user->update(['password' => Hash::make($request->password)]);
        return response()->json(['message' => 'Password updated successfully.']);
    }

    public function uploadAvatar(Request $request): JsonResponse {
        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);
        $user = $request->user();
        $profile = $user->profile;
        if (!$profile) { 
            $profile = $user->profile()->create(['full_name' => $user->name]); 
        }
        if ($profile->avatar_path) { 
            Storage::disk('public')->delete($profile->avatar_path); 
        }
        $path = $request->file('avatar')->store('avatars/' . $user->id, 'public');
        $profile->update(['avatar_path' => $path]);
        return response()->json(['avatar_url' => Storage::disk('public')->url($path)]);
    }

    public function deleteAccount(Request $request): JsonResponse {
        $request->validate(['password' => ['required', 'current_password']]);
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();
        return response()->json(['message' => 'Account deleted.']);
    }
}
