<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

// ---- Public reads (catalogue + detailed product page) ----
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/{product}/variants', [ProductVariantController::class, 'index']);
Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
Route::post('products/{product}/reviews', [ReviewController::class, 'store']);

// ---- Admin writes (catalogue management) ----
// To protect: Route::middleware('auth:sanctum')->group(function () { ... });
Route::group([], function () {
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::patch('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);

    Route::post('products/{product}/variants', [ProductVariantController::class, 'store']);
    Route::put('variants/{variant}', [ProductVariantController::class, 'update']);
    Route::patch('variants/{variant}', [ProductVariantController::class, 'update']);
    Route::delete('variants/{variant}', [ProductVariantController::class, 'destroy']);
});