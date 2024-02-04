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
        // ikutin contoh dari alfi => airlangga

        // cek user udah login apa belum 
        if (Auth::check()) {
            return redirect('/login');
        }

        // ambil user informasi dari google
        $oauthUser = Socialite::driver('google')->user();
        
        // cek kalau ada user dengan id yang sama udah ada (registered
        $user = User::where('google_id', $oauthUser->id)->first();

        if ($user) {
            // kalau google idnya ketemu, masuk
            Auth::loginUsingId($user->id);
            return redirect('/home');
        } else {
            // cek kalau user sign in pake email yg sama (udah register pake email a terus sign in google pake email a juga)
            $registeredUser = User::where('email', $oauthUser->email)->first();

            // kind user
            $kind = $request->input('kind', 'user');

            if ($registeredUser) {
                // kalau emailnya sama (yg registered dan dipake sign in)
                // update google idnya (null->google_id after sign in)
                $registeredUser->update(['google_id' => $oauthUser->id]);

                // Login user
                Auth::login($registeredUser);
                return redirect('/home');
            } else {
                // create user baru dari informasi user dr google
                $newUser = User::create([
                    'namaLengkap' => $oauthUser->name,
                    'email' => $oauthUser->email,
                    'social_id'=> $oauthUser->id,
                    'password' => bcrypt($oauthUser->token),
                    // Additional fields similar to your registerController
                    'tanggalLahir' => $request->input('tanggalLahir'),
                    'telepon' => $request->input('telepon'),
                    'kota' => $request->input('kota'),
                    'pekerjaan' => $request->input('pekerjaan'),
                    'kind' => $kind,  
                ]);

                // Log in user
                Auth::login($newUser);
                return redirect('/home');
            }
        }
    }
}




                