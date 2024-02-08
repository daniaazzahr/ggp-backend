<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // REGISTER
    public function registerUser(Request $request){

        DB::beginTransaction();
    
        //try (success) catch (error)
        try{
            // validate data => namaLengkap & email required
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users',
                'namaLengkap' => 'required|string|max:191',
                'tanggalLahir' => 'nullable|date',
                'telepon' => 'nullable|string',
                'kota' => 'nullable|string',
                'pekerjaan' => 'nullable|string',
                'password' => 'required|min:8',
                'kind' => 'nullable|in:admin,user', // new col. role as admin atau user
            ], [
                // validator custom error messages
                'namaLengkap.required' => 'Kolom Nama Lengkap tidak boleh kosong',
                'email.required' => 'Kolom Email tidak boleh kosong',
                'password.required' => 'Kolom Password tidak boleh kosong',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'messages' => $validator->errors(),
                    'data' => null,
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            // def value. kind = user
            $kind = $request->input('kind', 'user');
    
            // create user
            $user = User::create([
                'namaLengkap' => $request->input('namaLengkap'),
                'tanggalLahir' => $request->input('tanggalLahir'),
                'email' => $request->input('email'),
                'telepon' => $request->input('telepon'),
                'kota' => $request->input('kota'),
                'pekerjaan' => $request->input('pekerjaan'),
                'password' => bcrypt($request->input('password')),
                'kind' => $kind,
            ]);
            
            // kalau udah ok semua, db commit
            DB::commit();

            
            // return success
            return response()->json([
                'success' => true,
                'messages' => 'User ditambahkan',
                'data' => $user,
            ], JsonResponse::HTTP_CREATED);
    
        } 
        catch (Exception $e){
            DB::rollback();

            // return false
            return response()->json([
                'success' => false,
                'messages' => $e->getMessage(),
                'data' => null,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // LOGIN
    public function loginUser(Request $request){

        // validate
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ], [
            // validator custom error messages
            'email.required' => 'Kolom Email tidak boleh kosong',
            'password.required' => 'Kolom Password tidak boleh kosong',
        ]);

        // condition validation
        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        } 

        // get credentials dari request
        $credentials = $request->only('email', 'password');

        // if auth gagal
        if(!$token = auth()->guard('api')->attempt($credentials)){
            return response()->json([
                'isSuccess' => false,
                'message' => 'Email atau Password Salah!'
            ],401);
        } else{
            return response()->json([
                'isSuccess' => true,
                'message' => 'Login berhasil!',
                'user' => auth()->guard('api')->user(),
                'token' => $token
            ],200);
        }
    }

    // LOGOUT
    public function logoutUser(Request $request){

        // remove token
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        // kondisi when $removetoken true
        if($removeToken){
            return response()->json([
                'isSuccess' => true,
                'message' => 'Logout berhasil!'
            ]);
        }
    }

    
    
}
