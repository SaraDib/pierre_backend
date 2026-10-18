<?php

use App\Http\Controllers\Api\AcceuilController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DevisController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\MateriauxController;
use App\Http\Controllers\Api\MethodeController;
use App\Http\Controllers\Api\RealisationsController;
use App\Http\Controllers\Api\SavoirFaireController;
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
Route::get('/company', [CompanyController::class, 'index']);
Route::get('/savoir-faire', [SavoirFaireController::class, 'index']);
Route::get('/materiaux', [MateriauxController::class, 'index']);
Route::get('/realisations', [RealisationsController::class, 'index']);
Route::get('/methode', [MethodeController::class, 'index']);
Route::get('/faq', [FaqController::class, 'index']);
Route::get('/devis/section', [DevisController::class, 'getSection']);
Route::post('/devis', [DevisController::class, 'store']);

// Protected routes (Admin / Authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/admin/logout', [AuthController::class, 'logout']);

    // Admin Devis & Quotes Management
    Route::get('/admin/devis/section', [DevisController::class, 'getSection']);
    Route::post('/admin/devis/section', [DevisController::class, 'updateSection']);
    Route::get('/admin/devis', [DevisController::class, 'index']);
    Route::get('/admin/devis/stats', [DevisController::class, 'stats']);
    Route::post('/admin/devis/{devi}', [DevisController::class, 'update']);
    Route::delete('/admin/devis/{devi}', [DevisController::class, 'destroy']);

    // Admin Company Settings Management
    Route::get('/admin/company', [CompanyController::class, 'index']);
    Route::post('/admin/company', [CompanyController::class, 'update']);
    Route::post('/admin/company/reset', [CompanyController::class, 'reset']);

    // Admin Acceuil Content Management
    Route::post('/admin/acceuil', [AcceuilController::class, 'update']);
    Route::post('/admin/acceuil/reset', [AcceuilController::class, 'reset']);

    // Admin Savoir-faire Content Management
    Route::get('/admin/savoir-faire', [SavoirFaireController::class, 'adminIndex']);
    Route::post('/admin/savoir-faire/section', [SavoirFaireController::class, 'updateSection']);
    Route::post('/admin/savoir-faire/reorder', [SavoirFaireController::class, 'reorder']);
    Route::post('/admin/savoir-faire/items', [SavoirFaireController::class, 'store']);
    Route::post('/admin/savoir-faire/items/{item}', [SavoirFaireController::class, 'update']);
    Route::delete('/admin/savoir-faire/items/{item}', [SavoirFaireController::class, 'destroy']);

    // Admin Matériaux Content Management
    Route::get('/admin/materiaux', [MateriauxController::class, 'adminIndex']);
    Route::post('/admin/materiaux/section', [MateriauxController::class, 'updateSection']);
    Route::post('/admin/materiaux/reorder', [MateriauxController::class, 'reorder']);
    Route::post('/admin/materiaux/items', [MateriauxController::class, 'store']);
    Route::post('/admin/materiaux/items/{item}', [MateriauxController::class, 'update']);
    Route::delete('/admin/materiaux/items/{item}', [MateriauxController::class, 'destroy']);
    Route::post('/admin/materiaux/categories/reorder', [MateriauxController::class, 'reorderCategories']);
    Route::post('/admin/materiaux/categories', [MateriauxController::class, 'storeCategory']);
    Route::post('/admin/materiaux/categories/{category}', [MateriauxController::class, 'updateCategory']);
    Route::delete('/admin/materiaux/categories/{category}', [MateriauxController::class, 'destroyCategory']);

    // Admin Réalisations Content Management
    Route::get('/admin/realisations', [RealisationsController::class, 'adminIndex']);
    Route::post('/admin/realisations/section', [RealisationsController::class, 'updateSection']);
    Route::post('/admin/realisations/reorder', [RealisationsController::class, 'reorder']);
    Route::post('/admin/realisations/items', [RealisationsController::class, 'store']);
    Route::post('/admin/realisations/items/{item}', [RealisationsController::class, 'update']);
    Route::delete('/admin/realisations/items/{item}', [RealisationsController::class, 'destroy']);
    Route::post('/admin/realisations/categories/reorder', [RealisationsController::class, 'reorderCategories']);
    Route::post('/admin/realisations/categories', [RealisationsController::class, 'storeCategory']);
    Route::post('/admin/realisations/categories/{category}', [RealisationsController::class, 'updateCategory']);
    Route::delete('/admin/realisations/categories/{category}', [RealisationsController::class, 'destroyCategory']);

    // Admin Méthode Content Management
    Route::get('/admin/methode', [MethodeController::class, 'adminIndex']);
    Route::post('/admin/methode/section', [MethodeController::class, 'updateSection']);
    Route::post('/admin/methode/reorder', [MethodeController::class, 'reorder']);
    Route::post('/admin/methode/items', [MethodeController::class, 'store']);
    Route::post('/admin/methode/items/{item}', [MethodeController::class, 'update']);
    Route::delete('/admin/methode/items/{item}', [MethodeController::class, 'destroy']);

    // Admin FAQ Content Management
    Route::get('/admin/faq', [FaqController::class, 'adminIndex']);
    Route::post('/admin/faq/section', [FaqController::class, 'updateSection']);
    Route::post('/admin/faq/reorder', [FaqController::class, 'reorder']);
    Route::post('/admin/faq/items', [FaqController::class, 'store']);
    Route::post('/admin/faq/items/{item}', [FaqController::class, 'update']);
    Route::delete('/admin/faq/items/{item}', [FaqController::class, 'destroy']);
    Route::post('/admin/faq/categories/reorder', [FaqController::class, 'reorderCategories']);
    Route::post('/admin/faq/categories', [FaqController::class, 'storeCategory']);
    Route::post('/admin/faq/categories/{category}', [FaqController::class, 'updateCategory']);
    Route::delete('/admin/faq/categories/{category}', [FaqController::class, 'destroyCategory']);
});
