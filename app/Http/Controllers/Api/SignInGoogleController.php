<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Exception\ClientException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class SignInGoogleController extends Controller
{
    // redirect google
    public function redirect(){
        //original 

        // Session::flush();
        // return Socialite::driver('google')->redirect();

        // tutor yang react
         return response()->json([
             'url' => Socialite::driver('google')
                    ->stateless()
                    ->redirect()
                    ->getTargetUrl()
         ]);
    }


    // callback
    public function googleCallback( Request $request ){
        try {
            // minta socialite tolong ambil data user
            $google_user = Socialite::driver('google')->stateless()->user();
    
            // cek user emailnya udah ada didatabase apa belum
            $user = User::where('email', $google_user->getEmail())->first();
    
            if (!$user) {
                // kondisi email gaada di table user
                $new_user = User::create([
                    'namaLengkap' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'social_id' => $google_user->getId(),
                ]);
    
                // login user
                Auth::login($new_user);
    
                // generate jwt token
                $tokenBaru = JWTAuth::fromUser($new_user);
    
                return response()->json([
                    'success' => true,
                    'token' => $tokenBaru,
                    'message' => 'Selamat Datang di Dashboard!',
                    'data' => $new_user
                ], 201);
            } else {
                // kondisi email ada di table user (user udah register)
                Auth::login($user);
    
                // generate jwt token
                $tokenUser = JWTAuth::fromUser($user);

                // update social id user dan refresh
                $user->update(['social_id' => $google_user->getId()]);

                $updateUser = $user->refresh();
    
                return response()->json([
                    'success' => true,
                    'token' => $tokenUser,
                    'message' => 'Selamat Datang di Dashboard!',
                    'data' => $updateUser
                ], 200);
            } 
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
