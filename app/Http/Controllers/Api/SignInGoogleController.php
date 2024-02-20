<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class SignInGoogleController extends Controller
{
    // redirect
    public function redirect(){
        return Socialite::driver('google')->redirect();
    }

    // callback
    public function googleCallback(Request $request){
        try{
            // minta socialite tolong ambil data user
            $google_user = Socialite::driver('google')->user();

            // cek user udah punya google id apa belum
            $user = User::where('social_id', $google_user->getId())->first();

            // kondisi kalau user belum ada di db atau !$user, artinya idnya ga match
            if(!$user){
                // create gugle user
                $new_user = User::create([
                    'namaLengkap' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    // associate user ke google, store google id
                    'social_id' => $google_user->getId(),
                ]);

                // login user
                Auth::login($new_user);

                // generate token jwt
                $tokenBaru = JWTAuth::fromUser($new_user);

                // redirect user ke dashboard
                return response()->json([
                    'success' => true,
                    'token' => $tokenBaru,
                    'message' => 'Selamat Datang di Dashboard!',
                    'data' => $new_user
                ], 201);

            } else {
                // user exists di db, login user
                Auth::login($user);

                $tokenUser = JWTAuth::fromUser($user);

                // redirect user ke dashboard
                return response()->json([
                    'success' => true,
                    'token' => $tokenUser,
                    'message' => 'Selamat Datang di Dashboard!',
                    'data' => $user
                ], 200);
            }            

        } catch (Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
