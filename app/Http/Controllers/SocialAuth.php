<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialAuth extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $userSocial = Socialite::driver($provider)->user();
        print_r($userSocial);
        // $user = User::updateOrCreate(
        //     ['email' => $userSocial->getEmail()],
        //     ['name' => $userSocial->getName(), 'password' => bcrypt('password')]
        // );

        // $token = $user->createToken('authToken')->accessToken;

        // return response()->json(['token' => $token]);
    }
}
