<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Exception;

class RegistrasiController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        DB::beginTransaction();
    
        //try (success) catch (error)
        try{
            // validate data => namaLengkap & email required
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:191',
                'namaLengkap' => 'required|string|max:191',
                'tanggalLahir' => 'nullable|date',
                'telepon' => 'nullable|string',
                'kota' => 'nullable|string',
                'pekerjaan' => 'nullable|string',
                'password' => 'required|string',
            ]);
    
            // create user
            $user = User::create([
                'namaLengkap' => $request->input('namaLengkap'),
                'tanggalLahir' => $request->input('tanggalLahir'),
                'email' => $request->input('email'),
                'telepon' => $request->input('telepon'),
                'kota' => $request->input('kota'),
                'pekerjaan' => $request->input('pekerjaan'),
                'password' => bcrypt($request->input('password')),
            ]);
    
            // kalau udah ok semua, db commit
            DB::commit();
            // return
            // Return a success response
            return response()->json([
                'success' => true,
                'messages' => 'User ditambahkan',
                'data' => $user,
            ], JsonResponse::HTTP_CREATED);
    
        } 
        catch (Exception $e){
            DB::rollback();
            return response()->json([
                'success' => false,
                'messages' => $e->getMessage(),
                'data' => null,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
