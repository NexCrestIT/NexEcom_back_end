<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\GenderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ScentFamilyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes (can be protected with auth middleware if needed)
Route::prefix('v1')->group(function () {
    // Customer Authentication Routes (Public)
    Route::post('customers/register', [CustomerAuthController::class, 'register']);
    Route::post('customers/login', [CustomerAuthController::class, 'login']);
    
    // Customer Protected Routes (Bearer Token Authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('customers/logout', [CustomerAuthController::class, 'logout']);
        Route::get('customers/me', [CustomerAuthController::class, 'me']);
        
        // Cart Routes
        Route::prefix('cart')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\CartController::class, 'index']);
            Route::get('/summary', [\App\Http\Controllers\Api\CartController::class, 'summary']);
            Route::post('/', [\App\Http\Controllers\Api\CartController::class, 'store']);
            Route::put('/{id}', [\App\Http\Controllers\Api\CartController::class, 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\CartController::class, 'destroy']);
            Route::delete('/', [\App\Http\Controllers\Api\CartController::class, 'clear']);
        });
    });
    
    // Products API (Public)
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/featured', [ProductController::class, 'featured']);
    Route::get('products/new', [ProductController::class, 'newProducts']);
    Route::get('products/bestsellers', [ProductController::class, 'bestsellers']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    
    // Brands API
    Route::get('brands', [BrandController::class, 'index']);
    Route::get('brands/{id}', [BrandController::class, 'show']);
    
    // Genders API
    Route::get('genders', [GenderController::class, 'index']);
    Route::get('genders/{id}', [GenderController::class, 'show']);
    
    // Scent Families API
    Route::get('scent-families', [ScentFamilyController::class, 'index']);
    Route::get('scent-families/{id}', [ScentFamilyController::class, 'show']);
});

