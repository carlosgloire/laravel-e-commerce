<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    public function google_login()
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleAuthentification()
{
    try {
        $googleUser = Socialite::driver('google')->user();
        
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Create new user with Google data
            $user = User::create([
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => null, // No password for Google users
                'mobile' => null, // Mobile not required for Google users
                'email_verified_at' => now(),
                'utype' => 'USR', // Default user type
            ]);
        } else {
            // Update existing user with Google ID if they registered via form first
            if (empty($user->google_id)) {
                $user->update([
                    'google_id' => $googleUser->getId()
                ]);
            }
        }

        Auth::login($user, true);

        return redirect()->intended('/');

    } catch (\Exception $e) {
        return redirect()->route('login')->withErrors([
            'email' => 'Google authentication failed. Please try again.',
        ]);
    }
}
}