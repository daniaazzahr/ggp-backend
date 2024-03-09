<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ManagePicture;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PictureController extends Controller
{
    // upload gambar
    public function uploadImage(Request $request){
        DB::beginTransaction();

        try{
            // array kosong buat storing images
            $gambar = [];

            // validator 
            $validator = Validator::make($request->all(), [
                'images' => 'required',
                'images.*' => 'required|mimes:png,jpg,jpeg'
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 422);
            }

            // kondisi if yang diupload lebih dari 1 gambar
            if($request->has('images')){
                foreach($request->file('images') as $image){
                    $namaFile = time() . '_' . $image->getClientOriginalExtention();
                    // store ke public
                    $image->storeAs('pictures', $namaFile, 'public');

                    $gambar[] = $namaFile;

                    // create image di db
                    ManagePicture::create([
                        'image' => $namaFile
                    ]);
                }

                DB::commit();
                
                // response
                return response()->json([
                    'status' => true,
                    'message' => 'Foto berhasil di upload!',
                    'data' => $gambar
                ], 201);
            } else{
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'Foto gagal di upload'
                ], 422);
            }
        } catch (Exception $e){
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Foto gagal di upload'
            ], 422);
        }
    }

    public function upload(Request $request) {
        //$imagesName = [];
       // $response = [];
 
        $validator = Validator::make($request->all(),
            [
                'images' => 'required',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]
        );
 
        if($validator->fails()) {
            return response()->json(["status" => false, 
            "message" => $validator->errors(), 
            ]);
        }
 
        if($request->has('images')) {
            foreach($request->file('images') as $image) {
                $filename = Str::random(32).".".$image->getClientOriginalExtension();
                $image->storeAs('pictures', $filename, 'public');
                
 
                ManagePicture::create([
                    'image' => $filename
                ]);
            }
 
            // response
            return response()->json([
                'status' => true,
                'message' => 'Foto berhasil di upload!',
                'data' => $filename
            ], 201);
        }
 
        else {
            return response()->json([
                'status' => false,
                'message' => "Failed! image(s) not uploaded",
                
            ], 422);
        
        }
        //return response()->json($response);
    }

    // get pictures
    public function getPictures() {
        $images = ManagePicture::all();
        return response()->json([
            "status" => true, 
            "count" => count($images), 
            "data" => $images
        ], 200);
    }

    // get picture by id
    public function getPicture($id){
        // cari pic by id
        $carigambar = ManagePicture::find($id);

        if(!$carigambar){
            return response()->json([
                'success' => false,
                'message' => 'Gambar tidak ditemukan!'
            ], 422);
        } else{
            return response()->json([
                'success' => true,
                'message' => 'Gambar ditemukan!',
                'data' => $carigambar
            ], 200);
        }
    }

    // delete picture
    public function deletePicture($id){
        // cari pic by id
        $carigambar = ManagePicture::find($id);

        if(!$carigambar){
            return response()->json([
                'success' => false,
                'message' => 'Gambar tidak ditemukan!'
            ], 422);
        } else{

            $carigambar->delete();

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil dihapus!',
                'data' => $carigambar
            ], 200);
        }
    }
}
