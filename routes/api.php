<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\LicenseDownloadController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductSyncController;
use App\Http\Controllers\Api\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Internal sync endpoint (EA Hub → EA Store)
Route::middleware('internal.api')->post('/internal/products/sync', ProductSyncController::class);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/stripe/webhook', StripeWebhookController::class)->middleware('throttle:100,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/me/orders', [AccountController::class, 'orders']);
    Route::get('/me/licenses/{license}/download', LicenseDownloadController::class);
    Route::post('/checkout', [CheckoutController::class, 'store']);
});
