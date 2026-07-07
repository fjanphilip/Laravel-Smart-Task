<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        if (!in_array($provider, ['google', 'github'])) {
            return response()->json([
                'success' => false,
                'message' => 'Provider tidak didukung.',
            ], 422);
        }

        return redirect(Socialite::driver($provider)->stateless()->redirect()->getTargetUrl());
    }

    public function handleProviderCallback($provider)
    {
        if (!in_array($provider, ['google', 'github'])) {
            return response()->json([
                'success' => false,
                'message' => 'Provider tidak didukung.',
            ], 422);
        }

        $socialiteUser = Socialite::driver($provider)->stateless()->user();

        $user = User::updateOrCreate([
            'email' => $socialiteUser->getEmail(),
        ], [
            'name' => $socialiteUser->getName(),
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            'email_verified_at' => now(),
        ]);

        // Otomatis assign role default: 'developer'
        if (!$user->role) {
            $user->role = 'developer';
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect(config('services.frontend.url') . "?token=" . $token);
    }
}
