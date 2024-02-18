<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use App\Models\KonsultasiOnline;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class KonsultasiOnlineController extends Controller
{
    // POST Consultation
    public function postConsultation(Request $request){
        DB::beginTransaction();
        
        try{
            // validator
            $validator = Validator::make($request->all(), [
                'nama' => 'required',
                'namaid' => 'nullable',
                'kategori' => 'nullable',
                'buktiTransaksi' => 'required|image|mimes:jpeg,png,jpg',
                'pesanKonsultasi' => 'required|max:7000',
                'telepon'=> 'required',
                'email' => 'required',
                'jenisClient' => 'required',
                'kota' => 'nullable', 
                'alamat' => 'nullable', 
                'provinsi' => 'nullable', 
                // if perusahaan, namaPerusahaan required
                'namaPerusahaan' => $request->input('jenisClient') == 'Perusahaan' ? 'required' : 'nullable',
            ], [
                // validator custom error messages
                'nama.required' => 'Kolom Nama tidak boleh kosong',
                'buktiTransaksi.required' => 'Wajib unggah Bukti Transaksi',
                'pesanKonsultasi.required' => 'Kolom Pesan Konsultasi tidak boleh kosong',
                'telepon.required' => 'Nomor Telepon tidak boleh kosong',
                'email.required' => 'Kolom Email tidak boleh kosong',
                'jenisClient.required' => 'Wajib mencantumkan Jenis Klien',
                'namaPerusahaan.required' => 'Wajib mencantumkan Nama Perusahaan',
            ]);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ], 401);
            }

            // cek stored file image buktiTransaksi
            if($request->hasFile('buktiTransaksi')){
                $originalNamaFile = $request->file('buktiTransaksi')->getClientOriginalName();
                $buktiImg = $request->file('buktiTransaksi')->storeAs('buktiTransaksiIMG', $originalNamaFile, 'public');
            } else{
                return response()->json([
                    'success' => false,
                    'message' => 'Bukti Transaksi tidak ditemukan.',
                    'data' => null
                ], 422);
            }

            // auth user
            $user = Auth::guard('api')->user();

            // if user auth
            if($user) {
                $consultationUser = KonsultasiOnline::create([
                    // ambil dari database
                    'nama' => $user->namaLengkap,
                    'namaid' => $user->id,
                    'telepon' => $user->telepon,
                    'email' => $user->email,
                    'kota' => $user->kota,
                    'kategori' => $request->input('kategori'),
                    // ambil dari inputan
                    'alamat' => $request->input('alamat'),
                    'provinsi' => $request->input('provinsi'),
                    'pesanKonsultasi' => $request->input('pesanKonsultasi'),
                    'buktiTransaksi' => $buktiImg,
                    'jenisClient' => $request->input('jenisClient'),
                    'namaPerusahaan' => $request->input('namaPerusahaan'),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Konsultasi berhasil diajukan!',
                    'data' => $consultationUser
                ], 201);
            } else{
                $consultation = KonsultasiOnline::create([
                    // ambil dari database
                    'nama' => $request->input('nama'),
                    'namaid' => $request->input('namaid'),
                    'telepon' => $request->input('telepon'),
                    'email' =>$request->input('email'),
                    'kota' => $request->input('kota'),
                    'kategori' => $request->input('kategori'),
                    // ambil dari inputan
                    'alamat' => $request->input('alamat'),
                    'provinsi' => $request->input('provinsi'),
                    'pesanKonsultasi' => $request->input('pesanKonsultasi'),
                    'buktiTransaksi' => $buktiImg,
                    'jenisClient' => $request->input('jenisClient'),
                    'namaPerusahaan' => $request->input('namaPerusahaan'),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Konsultasi berhasil diajukan!',
                    'data' => $consultation
                ], 201);
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

    // UPDATE Consultation
    public function updateConsultation(Request $request, $id){
        DB::beginTransaction();

        try{
            // ambil data consultation based on id
            $data = KonsultasiOnline::find($id);

            if(!$data){
                return response()->json([
                    'success' => false,
                    'message' => 'Konsultasi tidak ditemukan!',
                    'data' => null
                ], 422);
            }

            // update data. status=> verifikasi -> pencarian advokat
            if($data->status == 'Menunggu Verifikasi'){
                $data->update([
                    'nama' => $data->nama,
                    'kategori' => $data->kategori,
                    'jenisClient' => $data->jenisClient,
                    'buktiTransaksi' => $data->buktiTransaksi,
                    'pesanKonsultasi' => $data->pesanKonsultasi,
                    'advokat' => 'Belum Ada Advokat',
                    'status' => 'Pencarian Advokat'
                ]);

                $updateVerifikasi = $data->refresh();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Verified! Status Anda: Pencarian Advokat',
                    'data' => $updateVerifikasi
                ], 201);
            }

            // update data. status=> pencarian advokat -> menunggu advokat
            if($data->status == 'Pencarian Advokat'){
                // validator advokat field
                $validator = Validator::make($request->all(), [
                    'advokat' => 'required'
                ]);

                if($validator->fails()){
                    return response()->json([
                        'success' => false,
                        'message'=> 'Kolom Advokat tidak boleh kosong',
                    ], 422);
                }

                // update data
                $data->update([
                    'nama' => $data->nama,
                    'kategori' => $data->kategori,
                    'jenisClient' => $data->jenisClient,
                    'buktiTransaksi' => $data->buktiTransaksi,
                    'pesanKonsultasi' => $data->pesanKonsultasi,
                    'advokat' => $request->input('advokat'),
                    'status' => 'Menunggu Advokat',
                ]);

                $updateAdvokat = $data->refresh();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Final. Status Anda: Menunggu Advokat',
                    'data' => $updateAdvokat
                ], 201);
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

    // DOWNLOAD Bukti transaksi
    public function downloadBuktiTransaksi($id){
        // cari konsultasi based on id
        $carikonsultasiID = KonsultasiOnline::find($id);

        if(!$carikonsultasiID){
            return response()->json([
                'success' => false,
                'message' => 'Konsultasi tidak ditemukan!'
            ], 422);
        }

        // ambil file bukti transaksi dari filepath public storage
        $file = storage_path("app/public/{$carikonsultasiID->buktiTransaksi}");

        // cek existence file
        if(!file_exists($file)){
            return response()->json([
                'success' => false,
                'message' => 'File Bukti Transaksi tidak ditemukan!'
            ], 422);
        }

        // return reponse untuk download
        return response()->download($file);
    }

    // DELETE Consultation
    public function deleteConsultation($id){
        // cari konsultasi based on id
        $carikonsultasiID = KonsultasiOnline::find($id);

        if(!$carikonsultasiID){
            return response()->json([
                'success' => false,
                'message' => 'Konsultasi tidak ditemukan!'
            ], 422);
        } else{
            // delete konsultasi
            $carikonsultasiID->delete();

            return response()->json([
                'success' => true,
                'message' => 'Konsultasi berhasil dihapus!',
                'data' => $carikonsultasiID
            ], 200);
        }
    }

    // GET Consultation by ID
    public function getConsultation($id) {
        // cari konsultasi based on id
        $getkonsultasiID = KonsultasiOnline::find($id);

        if(!$getkonsultasiID){
            return response()->json([
                'success' => false,
                'message' => 'Konsultasi tidak ditemukan!'
            ], 422);
        } else{


            return response()->json([
                'success' => true,
                'message' => 'Konsultasi berhasil ditemukan!',
                'data' => $getkonsultasiID
            ], 200);
        }
    }

    // GET Consultations - filter
    public function getConsultations(Request $request){
        // page, take, orderBy, order, search
        try{
            
            // 1. ambil input dari http request
            $page = $request->query('page', 1);
            $take = $request->query('take', 2);
            $orderBy = $request->query('orderBy', 'id');
            $order = $request->query('order', 'ASC');
            $search = $request->query('search', null);
            $status = $request->query('status', '');
            
            // 2. initialize query
            $query = KonsultasiOnline::query();
            // initialize query per status
            $queryMenungguVerif = KonsultasiOnline::query()->where('status', 'Menunggu Verifikasi');
            $queryPencarianAdvokat = KonsultasiOnline::query()->where('status', 'Pencarian Advokat');
            $queryMenungguAdvokat = KonsultasiOnline::query()->where('status', 'Menunggu Advokat');


            // 3. filter search
            if ($search !== null){
                $query->where('nama', 'like', '%' . $search . '%');
                // search by nama per status
                $queryMenungguVerif->where('nama', 'like', '%'. $search . '%');
                $queryPencarianAdvokat->where('nama', 'like', '%'. $search . '%');
                $queryMenungguAdvokat->where('nama', 'like', '%'. $search . '%');
            
            }

            // kondisi filtering per status

            $statusSlug = null;
            
            if ($status == 'Menunggu Verifikasi') {
                $query = $queryMenungguVerif;
                $statusSlug = 'menunggu-verifikasi';
            } elseif ($status == 'Pencarian Advokat') {
                $query = $queryPencarianAdvokat;
                $statusSlug = 'pencarian-advokat';
            } elseif ($status == 'Menunggu Advokat') {
                $query = $queryMenungguAdvokat;
                $statusSlug = 'menunggu-advokat';
            }

            // bikin url ketika filtering by status provided
            $url = $statusSlug ? $request->fullUrlWithQuery(['status' => $statusSlug]) : $request->url();

            // 4. sort query
            $query->orderBy($orderBy, $order);
            // sort query per status
            $queryMenungguVerif->orderBy($orderBy, $order);
            $queryPencarianAdvokat->orderBy($orderBy, $order);
            $queryMenungguAdvokat->orderBy($orderBy, $order);


            // 5. pagination
            $data = $query->skip(($page - 1) * $take)->take($take)->get();
            // pagination buat per status
            // $dataMenungguVerifikasi = $queryMenungguVerif->skip(($page - 1) * $take)->take($take)->get();
            // $dataPencarianAdvokat = $queryPencarianAdvokat->skip(($page - 1) * $take)->take($take)->get();
            // $dataMenungguAdvokat = $queryMenungguAdvokat->skip(($page - 1) * $take)->take($take)->get();

            // 6. count query
            $totalData = KonsultasiOnline::count();
            // count data per status
            $totalMenungguVerifikasi = $queryMenungguVerif->count();
            $totalPencarianAdvokat = $queryPencarianAdvokat->count();
            $totalMenungguAdvokat = $queryMenungguAdvokat->count();

            // 7. total pages
            $totalPages = ceil($totalData/$take);
            // total pages per status
            // $pagesMenungguVerifikasi = ceil($totalMenungguVerifikasi/$take);
            // $pagesPencarianAdvokat = ceil($totalPencarianAdvokat/$take);
            // $pagesMenungguAdvokat = ceil($totalMenungguAdvokat/$take);
            
            // 8. next dan prev page
            $nextPage = $page < $totalPages;
            $prevPage = $page > 1;
            // next dan prev page per status
            // $nextPageMenungguVerif = $page < $pagesMenungguVerifikasi;
            // $prevPageMenungguVerif = $page > 1;
            // $nextPagePencarianAdvokat = $page < $pagesPencarianAdvokat;
            // $prevPagePencarianAdvokat = $page > 1;
            // $nextPageMenungguAdvokat = $page < $pagesMenungguAdvokat;
            // $prevPageMenungguAdvokat = $page > 1;

            // 9. default ke page 1 ketika search
            if($search){
                $page = 1;
            }

            // 10. return data
            return response()->json([
                'success' => true,
                'status' => $status,
                'statusSlug' => $statusSlug,
                'url' => $url,
                'totalData' => $totalData,
                'totalMenungguVerifikasi' => $totalMenungguVerifikasi,
                'totalPencarianAdvokat' => $totalPencarianAdvokat,
                'totalMenungguAdvokat' => $totalMenungguAdvokat,
                'page' => $page,
                'take' => $take,
                'orderBy' => $orderBy,
                'order' => $order,
                'search' => $search,
                'totalPages' => $totalPages,
                // 'totalPagesMenungguVerif' => $pagesMenungguVerifikasi,
                // 'totalPagesPencarianAdvokat' => $pagesPencarianAdvokat,
                // 'totalPagesMenungguAdvokat' => $pagesMenungguAdvokat,
                'nextPage' => $nextPage,
                'prevPage' => $prevPage,
                // 'nextPageMenungguVerifikasi' => $nextPageMenungguVerif,
                // 'prevPageMenungguVerifikasi' => $prevPageMenungguVerif,
                // 'nextPagePencarianAdvokat' => $nextPagePencarianAdvokat,
                // 'prevPagePencarianAdvokat' => $prevPagePencarianAdvokat,
                // 'nextPageMenungguAdvokat' => $nextPageMenungguAdvokat,
                // 'prevPageMenungguAdvokat' => $prevPageMenungguAdvokat,
                'data' => $data,   
                // 'dataMenungguVerifikasi' => $dataMenungguVerifikasi,
                // 'dataPencarianAdvokat' => $dataPencarianAdvokat,
                // 'dataMenungguAdvokat' => $dataMenungguAdvokat,             
            ], 200);
        } catch (Exception $e){
            return response()->json([
                'succees' => false, 
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        }
    }
}


