<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KlinikHukumController;
use App\Http\Controllers\Api\KonsultasiOnlineController;
use App\Http\Controllers\Api\ManageUserController;
use App\Models\KonsultasiOnline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::group(['middleware' => ['auth.jwt']],function (){
    // sign in gugel
    Route::get('google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('google/callback', [GoogleController::class, 'handleCallback']);
});


// ========================== USERS CRUD ROUTES ==================================

//Route::middleware('api')->get('/users', [ManageUserController::class, 'getUsers']);
Route::get('/users', [ManageUserController::class, 'getUsers']);
Route::get('/user/{id}', [ManageUserController::class, 'getUser']);
Route::middleware(['auth:api'])->group(function () {


    Route::put('/user/{id}', [ManageUserController::class, 'editUser']);
    Route::delete('/user/{id}', [ManageUserController::class, 'deleteUser']);
});


// =========================== KLINIK HUKUM ===========================
Route::post('/clinic', [KlinikHukumController::class, 'createPertanyaan']);
Route::group(['middleware' => 'auth:api'], function () {

    Route::put('/clinic/{id}', [KlinikHukumController::class, 'jawabPertanyaan']);
    Route::delete('/clinic/{id}', [KlinikHukumController::class, 'deletePertanyaan']);
    
});    
Route::get('/clinic/{id}', [KlinikHukumController::class, 'getPertanyaan']);
Route::get('/clinics/{isAnswer}', [KlinikHukumController::class, 'getClinics']);


// ======================== KONSULTASI ONLINE ===========================
Route::post('consultation', [KonsultasiOnlineController::class, 'postConsultation']);
Route::put('consultation/{id}', [KonsultasiOnlineController::class, 'updateConsultation']);
Route::get('download/{id}', [KonsultasiOnlineController::class, 'downloadBuktiTransaksi']);
Route::delete('consultation/{id}', [KonsultasiOnlineController::class, 'deleteConsultation']);
Route::get('consultation/{id}', [KonsultasiOnlineController::class, 'getConsultation']);
Route::get('consultations', [KonsultasiOnlineController::class, 'getConsultations']);





