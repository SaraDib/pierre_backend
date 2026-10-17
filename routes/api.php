<?php

use App\Http\Controllers\Api\AcceuilController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'login']);
Route::get('/acceuil', [AcceuilController::class, 'index']);

// Protected routes (Admin / Authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/admin/logout', [AuthController::class, 'logout']);

    // Admin Acceuil Content Management
    Route::post('/admin/acceuil', [AcceuilController::class, 'update']);
    Route::post('/admin/acceuil/reset', [AcceuilController::class, 'reset']);
});
