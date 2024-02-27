<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class SignInGoogleController extends Controller
{
    // redirect google
    public function redirect(){
        return response()->json([
            'url' => Socialite::driver('google')
                ->stateless()
                ->redirect()
                ->getTargetUrl(),
        ]);
    }

    // callback
    public function googleCallback(Request $request){
        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver('google')->stateless()->user();
        } catch (ClientException $e) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

        /** @var User $user */
        $user = User::query()
            ->firstOrCreate(
                [
                    'email' => $socialiteUser->getEmail(),
                ],
                [
                    'email_verified_at' => now(),
                    'namaLengkap' => $socialiteUser->getName(),
                    'social_id' => $socialiteUser->getId(),
                ]
            );

        Auth::login($user);
        $tokenUser = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            'access_token' => $tokenUser,
            'token_type' => 'Bearer',
        ]);

    }
}
