<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // copied

    public function redirectToGoogle()
    {
        // redirect user ke "login with Google account" page
        return Socialite::driver('google')->redirect();
    }

    public function handleCallback(Request $request)
    {
        try {
            // Set guard 'api' sebelum otentikasi
            Auth::setDefaultDriver('api');

            // get user data dari google
            $user = Socialite::driver('google')->user();

            // cari user yg social_idnya sama kaya yg di provide google
            $finduser = User::where('social_id', $user->id)->first();

            if ($finduser)  // ketika user id ketemu
            {
                // User found in the database, log in the user
                // $token = JWTAuth::fromUser($finduser);

                // login user
                Auth::login($finduser);

                // redirect user ke home page
                return redirect('/home');
            }
            else
            {
                // $kind => default nilainya 'user'
                $kind = $request->input('kind', 'user');

                // kalo social id user ga ketemu atau ga match sama list data di db, dia berarti baru login pk gugel skarang
                // create user pake Google account data mereka, ditambah yg field registrasi
                $newUser = User::create([
                'namaLengkap' => $user->name,
                'email' => $user->email,
                'social_id' => $user->id,
                'social_type' => 'google',  // loginnya pake google
                'password' => bcrypt($user->token),  // Hash the token as the password

                // Additional fields similar to your registerController
                'tanggalLahir' => $request->input('tanggalLahir'),
                'telepon' => $request->input('telepon'),
                'kota' => $request->input('kota'),
                'pekerjaan' => $request->input('pekerjaan'),
                'kind' => $kind,  
                ]);

                // Log in the new user and return a JWT token
                //$token = JWTAuth::fromUser($newUser);

                Auth::login($newUser);

                return redirect('/home');
            }

        }
        catch (Exception $e)
        {
            // return error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
