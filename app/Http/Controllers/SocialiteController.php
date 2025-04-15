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
            
            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(24)), // Generate random password
                    'email_verified_at' => now(), // Google verified emails are trusted
                ]);
            }

            // Log in the user
            Auth::login($user, true);

            return redirect()->intended('/'); // Redirect to intended page or home

        } catch (\Exception $e) {
            // Handle exceptions (e.g., failed authentication)
            return redirect()->route('login')->withErrors([
                'email' => 'Google authentication failed. Please try again.',
            ]);
        }
    }
}