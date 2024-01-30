<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ManageUserController extends Controller
{
    // CRUD USERS CONTROLLER

    // getUsers => get all users pake filter juga
    public function getUsers(Request $request){
        // => page = 1, take = 10, orderBy = namaLengkap, order= desc, search= admin
        try {
            // Your existing code here...
            // 1. ambil input dari http req
            $page = $request->query('page', 1);
            $take = $request->query('take', 10);
            $orderBy = $request->query('orderBy', 'namaLengkap');
            $order = $request->query('order', 'ASC');
            $search = $request->query('search', null);

        // 2. inisialisasi query
        $query = User::query();

        // 3. filter search
        if($search){
            $query->where('namaLengkap', 'like', '%' . $search . '%');
        }

        // 4. sortir query
        $query->orderBy($orderBy, $order);

        // 5. pagination => ini yg diambil datanya
        $users = $query->skip(($page - 1) * $take)->take($take)->get();
        
        // 6. count query
        $total = User::count();

        // 7. total Halaman
        $totalHalaman = ceil($total / $take);

        // 8. pagination next dan prev page
        $nextPage = $page < $totalHalaman;
        $prevPage = $page > 1;

        // 9. return
        return response()->json([
        'success' => true,
        'page' => $page,
        'take' => $take,
        'orderBy' => $orderBy,
        'order' => $order,
        'search' => $search,
        'totalPages' => $totalHalaman,
        'hasNext' => $nextPage,
        'hasPrevious' => $prevPage,
        'data' => $users,
        ], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        }
        
    }

    // getUser by ID 
    public function getUser($id){

        // DB begin
        DB::beginTransaction();

        try{
            // cari user
            $findUser = User::find($id);

            if($findUser){
                DB::commit();
            }else{
                DB::rollback();

                return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan', 
                'data' => null,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
           
            return response()->json([
                'success' => true,
                'message' => 'User ditemukan!',
                'data' => $findUser
            ], JsonResponse::HTTP_OK);

        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan!', 
                'data' => null,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // editUser => user/id
    public function editUser(Request $request, $id) {
        // DB begin
        DB::beginTransaction();
    
        //try (success) catch (error)
        try{
            // validate data => namaLengkap & email required
            $validator = Validator::make($request->all(), [
                
                'namaLengkap' => 'required|string|max:191',
                'tanggalLahir' => 'nullable|date',
                'telepon' => 'nullable|string',
                'kota' => 'nullable|string',
                'pekerjaan' => 'nullable|string',
                'password' => 'required|string',
            ]);
    
            // CARI user by id
            $cariuser = User::find($id);
            
            $cariuser->update([
                'namaLengkap' => $request->input('namaLengkap'),
                'tanggalLahir' => $request->input('tanggalLahir'),
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
                'messages' => 'User berhasil di-update!',
                'data' => $cariuser,
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

    // deleteUser => user/id
    public function deleteUser($id) {
        
        DB::beginTransaction();

        try{
            $finduser = User::find($id);
        
            // condition
            if($finduser){
                $finduser->delete();
                
                DB::commit();
                
                // Return a success response
                return response()->json([
                    'success' => true,
                    'messages' => 'User berhasil dihapus!',
                    'data' => $finduser,
                ], JsonResponse::HTTP_CREATED);

            } else {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'messages' => 'User tidak ditemukan!',
                    'data' => null,
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (Exception $e){
            DB::rollback();

            return response()->json([
                'success' => false,
                'messages' => $e->getMessage(),
                'data' => null,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        
    }

    
}
