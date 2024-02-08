<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\KlinikHukum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr;

class KlinikHukumController extends Controller
{
    // Post pertanyaan
    public function createPertanyaan(Request $request){

        DB::beginTransaction();

        try{
            // Validator, kategori not required
            $validator = Validator::make($request->all(),[
                'pertanyaan' => 'required|string',
                'kategori' => 'nullable',
                'penulis' => 'required'
            ], [
                // validator custom error messages
                'pertanyaan.required' => 'Kolom Pertanyaan tidak boleh kosong',
                'penulis.required' => 'Wajib isi nama Anda',
            ]);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors(),
                    'data' => null
                ], 422);
            }

            // auth user => guard api
            $user = Auth::guard('api')->user();

            // condition ketika user logged in
            if($user){
                // create post
                $postlogin = KlinikHukum::create([
                    'pertanyaan' => $request->input('pertanyaan'),
                    'kategori' => $request->input('kategori'),
                    'penulis' => $user->namaLengkap,
                    'penulisid' => $user->id
                ]);

                DB::commit();
                return response()->json([
                    'success' => true, 
                    'message' => 'Pertanyaan telah di post!',
                    'data' => $postlogin
                ], 201);
            } else{
                // create post juga
                $post = KlinikHukum::create([
                    'pertanyaan' => $request->input('pertanyaan'),
                    'kategori' => $request->input('kategori'),
                    'penulis' => $request->input('penulis'),
                    'penulisid' => null
                ]);

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Pertanyaan telah di post!',
                    'data' => $post
                ], 201);
            }
        } catch (Exception $e){
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        }
    }

    // EDIT POST => jawaban
    public function jawabPertanyaan(Request $request, $id){
        
        DB::beginTransaction();

        try{
            // Validator, isi field jawaban
            $validator = Validator::make($request->all(),[
                'jawaban' => 'required|string'
            ]);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'message' => 'Kolom Jawaban tidak boleh kosong',
                ], 422);
            }

            // auth user
            $user = Auth::guard('api')->user();

            // find pertanyaan id
            $caripertanyaan = KlinikHukum::find($id);

            if($caripertanyaan){
                // update tambahin pertanyaan
                $caripertanyaan->update([
                    'jawaban' => $request->input('jawaban'),
                    'pertanyaan' => $caripertanyaan->pertanyaan,
                    'penulis' => $caripertanyaan->penulis,
                    'penulisid' => $caripertanyaan->penulisid,
                    'isAnswer' => true,
                    'kategori' => $caripertanyaan->kategori
                ]);

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Jawaban telah di-post!',
                    'data' => $caripertanyaan
                ], 201);
            } else{
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Pertanyaan tidak ditemukan.'
                ], 422);
            }

         
        } catch (Exception $e){
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ],422);
        }
    }

    // DELETE PERTANYAAN 
    public function deletePertanyaan($id){
        DB::beginTransaction();

        try{
            // auth user
            $user= Auth::guard('api')->user();

            // cari pertanyaan id
            $caripertanyaan= KlinikHukum::find($id);

            if($caripertanyaan){
                $caripertanyaan->delete();

                DB::commit();
                return response()->json([
                    'success'=> true,
                    'message' => 'Pertanyaan berhasil dihapus',
                    'data' => $caripertanyaan
                ], 201);
            } else{
                
                return response()->json([
                    'success' => false,
                    'message' => 'Pertanyaan tidak ditemukan!'
                ], 422);
            }
        } catch (Exception $e){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        }
    }

    // Get pertanyaan by id
    public function getPertanyaan($id){
        DB::beginTransaction();

        try{
            // ga pake auth
            // cari pertanyaan id
            $caripertanyaan = KlinikHukum::find($id);

            // condition
            if($caripertanyaan){
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Pertanyaan ditemukan!',
                    'data' => $caripertanyaan
                ], 200);
            } else{
                return response()->json([
                    'success' => false,
                    'message' => 'Pertanyaan tidak ditemukan!'
                ], 422);
            }
        } catch(Exception $e){
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        }
    }

    // GET ALL PERTANYAAN (FILTER) - rev: isAnswer required
    public function getClinics(Request $request, $isAnswer){
        // page, take, orderBy, order
        
        try{
            // 1. ambil input dari httpreq
            $page = $request->query('page', 1);
            $take = $request->query('take', 10);
            $orderBy = $request->query('orderBy', 'id');
            $order = $request->query('order', 'ASC');
            $search = $request->query('search', null);

            // 2. initialize query
            $query = KlinikHukum::query();
            
            if($isAnswer == 'false'){
                $query->where('isAnswer', false);
            }

            if($isAnswer == 'true'){
                $query->where('isAnswer', true);
            }

            // 3. filter search - apapun input user
            if($search !== null){
                $query->where('pertanyaan', 'like', '%' . $search . '%');
            }

            // 6. count query
            $totalPertanyaan = $query->count();

            // 4. sort query
            $query->orderBy($orderBy, $order);
            
            // 5. pagination
            $data = $query->skip(($page - 1) * $take)->take($take)->get();

            // 7. total pages
            $totalPages = ceil($totalPertanyaan/$take);

            // 8. next dan prev page
            $nextPage = $page < $totalPages;
            $prevPage = $page > 1;

            if ($search) {
                $page = 1;
            }

            // 9. return 
            return response()->json([
                'success' => true, 
                'totalPertanyaan' => $totalPertanyaan,
                'page' => $page,
                'take' => $take,
                'orderBy' => $orderBy,
                'order' => $order,
                'search' => $search,
                'totalPages' => $totalPages,
                'nextPage' => $nextPage,
                'prevPage' => $prevPage,
                'data' => $data
            ], 200);
        } catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        }
    }
}
