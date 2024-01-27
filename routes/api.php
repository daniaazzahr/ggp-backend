<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ManageUserController;
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

Route::middleware('api')->get('/users', [ManageUserController::class, 'getUsers']);
Route::get('/user/{id}', [ManageUserController::class, 'getUser']);
Route::put('/user/{id}', [ManageUserController::class, 'editUser']);
Route::delete('/user/{id}', [ManageUserController::class, 'deleteUser']);

