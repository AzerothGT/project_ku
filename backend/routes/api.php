<?php


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);


    // Profil user yang sedang login
    Route::get('/user', function (Request $request) {
        return response()->json([
            'message' => 'Profil user berhasil diambil',
            'data' => $request->user(),
        ]);
    });
    Route::match(['put', 'patch'], '/user', [UserController::class, 'updateProfile']);
    Route::delete('/user', [UserController::class, 'destroyProfile']);


    // Manajemen User berdasarkan ID
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::match(['put', 'patch'], '/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
