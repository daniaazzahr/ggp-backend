<?php

use Illuminate\Http\Request;
use App\Models\KonsultasiOnline;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ManageUserController;
use App\Http\Controllers\Api\KlinikHukumController;
use App\Http\Controllers\Api\SignInGoogleController;
use App\Http\Controllers\Api\KonsultasiOnlineController;
use App\Http\Controllers\Api\ManagePictures;
use App\Http\Controllers\Api\PictureController;
use App\Models\ManagePicture;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ========================== AUTHENTICATION ROUTES ==============================

//Route::post('/register', App\Http\Controllers\Api\RegistrasiController::class)->name('register');
Route::post('/registrasi', [AuthController::class, 'registerUser']);
Route::post('/login', [AuthController::class, 'loginUser']);
Route::middleware('auth:api')->get('/user', function(Request $request){
    return $request->user();
});
Route::post('/logout', [AuthController::class, 'logoutUser']);

// route sign in with google
Route::get('auth/google', [SignInGoogleController::class, 'redirect'])->name('google-auth');
Route::get('auth/google/call-back', [SignInGoogleController::class, 'googleCallback']);
//Route::get('auth/google/call-back', [SignInGoogleController::class, 'cobaCallback']);



// ========================== USERS CRUD ROUTES ==================================

//Route::middleware('api')->get('/users', [ManageUserController::class, 'getUsers']);
Route::get('/users', [ManageUserController::class, 'getUsers']);
Route::get('/user/{id}', [ManageUserController::class, 'getUser']);
Route::middleware(['auth:api'])->group(function () {
    Route::post('/user/{id}', [ManageUserController::class, 'editUser']);
    Route::delete('/user/{id}', [ManageUserController::class, 'deleteUser']);
    // untuk dashboard user
    Route::get('/pengguna/dashboard', [ManageUserController::class, 'dashboardUser']);
});


// =========================== KLINIK HUKUM ===========================
Route::post('/clinic', [KlinikHukumController::class, 'createPertanyaan']);
Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/clinic/{id}', [KlinikHukumController::class, 'jawabPertanyaan']);
    Route::delete('/clinic/{id}', [KlinikHukumController::class, 'deletePertanyaan']);
    
});    
Route::get('/clinic/{id}', [KlinikHukumController::class, 'getPertanyaan']);
Route::get('/clinics/{isAnswer}', [KlinikHukumController::class, 'getClinics']);


// ======================== KONSULTASI ONLINE ===========================
Route::post('consultation', [KonsultasiOnlineController::class, 'postConsultation']);
Route::post('consultation/{id}', [KonsultasiOnlineController::class, 'updateConsultation']);
Route::get('download/{id}', [KonsultasiOnlineController::class, 'downloadBuktiTransaksi']);
Route::delete('consultation/{id}', [KonsultasiOnlineController::class, 'deleteConsultation']);
Route::get('consultation/{id}', [KonsultasiOnlineController::class, 'getConsultation']);
Route::get('consultations', [KonsultasiOnlineController::class, 'getConsultations']);


// ======================== UPLOAD GAMBAR ===========================
Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/upload-img', [PictureController::class, 'upload']);
    Route::delete('/delete-img/{id}', [PictureController::class, 'deletePicture']);
    
});  

Route::get('/img', [PictureController::class, 'getPictures']);
Route::get('/img/{id}', [PictureController::class, 'getPicture']);


