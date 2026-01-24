<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CarouselController;
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
        Route::get('customers/me', [CustomerAuthController::class, 'me']);
        Route::put('customers/me', [CustomerAuthController::class, 'update']);
        Route::post('customers/logout', [CustomerAuthController::class, 'logout']);

        // Cart Routes
        Route::prefix('cart')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\CartController::class, 'index']);
            Route::get('/summary', [\App\Http\Controllers\Api\CartController::class, 'summary']);
            Route::post('/', [\App\Http\Controllers\Api\CartController::class, 'store']);
            Route::put('/{id}', [\App\Http\Controllers\Api\CartController::class, 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\CartController::class, 'destroy']);
            Route::delete('/', [\App\Http\Controllers\Api\CartController::class, 'clear']);
        });

        //wishlist routes
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\WishlistController::class, 'index']);
            Route::get('/summary', [\App\Http\Controllers\Api\WishlistController::class, 'summary']);
            Route::post('/', [\App\Http\Controllers\Api\WishlistController::class, 'store']);
            Route::put('/{id}', [\App\Http\Controllers\Api\WishlistController::class, 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\WishlistController::class, 'destroy']);
            Route::delete('/', [\App\Http\Controllers\Api\WishlistController::class, 'clear']);
        });

        Route::prefix('orders')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\OrderController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\OrderController::class, 'store']);
            Route::get('/pending', [\App\Http\Controllers\Api\OrderController::class, 'pending']);
            Route::get('/completed', [\App\Http\Controllers\Api\OrderController::class, 'completed']);
        });

        // Razorpay Payment Routes
        Route::prefix('razorpay')->group(function () {
            Route::post('/create-order', [\App\Http\Controllers\Api\RazorpayController::class, 'createOrder']);
            Route::post('/verify-payment', [\App\Http\Controllers\Api\RazorpayController::class, 'verifyPayment']);
            Route::post('/payment-failed', [\App\Http\Controllers\Api\RazorpayController::class, 'paymentFailed']);
            Route::get('/payment-status/{orderId}', [\App\Http\Controllers\Api\RazorpayController::class, 'getPaymentStatus']);
        });

        // Address Routes
        Route::prefix('addresses')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\AddressController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\AddressController::class, 'store']);
            Route::get('/{id}', [\App\Http\Controllers\Api\AddressController::class, 'show']);
            Route::put('/{id}', [\App\Http\Controllers\Api\AddressController::class, 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\AddressController::class, 'destroy']);
        });

        
    });

    Route::get('categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);

    // Products API (Public)
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/featured', [ProductController::class, 'featured']);
    Route::get('products/new', [ProductController::class, 'newProducts']);
    Route::get('products/bestsellers', [ProductController::class, 'bestsellers']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::put('products/{id}', [ProductController::class, 'update']);

    // Product Reviews API (Public - Get reviews)
    Route::get('products/{productId}/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'index']);
    Route::get('products/{productId}/reviews/rating/{rating}', [\App\Http\Controllers\Api\ReviewController::class, 'getByRating']);

    // Product Reviews API (Protected - Add/Update/Delete reviews)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('products/{productId}/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
        Route::put('reviews/{reviewId}', [\App\Http\Controllers\Api\ReviewController::class, 'update']);
        Route::delete('reviews/{reviewId}', [\App\Http\Controllers\Api\ReviewController::class, 'destroy']);
    });

    // Product Questions API (Public - Get questions)
    Route::get('products/{productId}/questions', [\App\Http\Controllers\Api\ProductQuestionController::class, 'index']);
    Route::get('products/{productId}/questions/answered', [\App\Http\Controllers\Api\ProductQuestionController::class, 'answered']);
    Route::get('questions/{questionId}', [\App\Http\Controllers\Api\ProductQuestionController::class, 'show']);

    // Product Questions API (Protected - Ask/Update/Delete questions)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('products/{productId}/questions', [\App\Http\Controllers\Api\ProductQuestionController::class, 'store']);
        Route::put('questions/{questionId}', [\App\Http\Controllers\Api\ProductQuestionController::class, 'update']);
        Route::delete('questions/{questionId}', [\App\Http\Controllers\Api\ProductQuestionController::class, 'destroy']);
        Route::post('questions/{questionId}/helpful', [\App\Http\Controllers\Api\ProductQuestionController::class, 'markHelpful']);
    });

    // Admin - Answer Questions
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('questions/{questionId}/answer', [\App\Http\Controllers\Api\ProductQuestionController::class, 'answer']);
    });

    // Brands API
    Route::get('brands', [BrandController::class, 'index']);
        Route::get('brands/featured', [BrandController::class, 'featured']);
    Route::get('brands/{id}', [BrandController::class, 'show']);

    // Genders API
    Route::get('genders', [GenderController::class, 'index']);
    Route::get('genders/{id}', [GenderController::class, 'show']);

    // Scent Families API
    Route::get('scent-families', [ScentFamilyController::class, 'index']);
    Route::get('scent-families/{id}', [ScentFamilyController::class, 'show']);

    // Carousel API (Public - Get active carousels)
    Route::get('carousels', [CarouselController::class, 'index']);

    // Carousel API (Admin - Manage carousels) - Must come before {id} route
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('carousels/admin/all', [CarouselController::class, 'getAll']);
        Route::post('carousels', [CarouselController::class, 'store']);
        Route::post('carousels/{id}/toggle-status', [CarouselController::class, 'toggleStatus']);
        Route::put('carousels/{id}', [CarouselController::class, 'update']);
        Route::delete('carousels/{id}', [CarouselController::class, 'destroy']);
    });

    // Carousel API (Public - Get single carousel) - Must be after admin routes
    Route::get('carousels/{id}', [CarouselController::class, 'show']);
});
