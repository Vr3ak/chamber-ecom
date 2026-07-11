<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TrendingController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Chamber API routes  (prefixed with /api by the framework)
|--------------------------------------------------------------------------
| Feature 1 — Detailed product page (+ shoe CRUD)
| Feature 2 — Filter / search
| Feature 3 — Orders + payments (KHQR / credit card)
|
| Public reads are open. Catalogue writes need an admin token (Sanctum).
*/

// ---- Admin auth ----
Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('admin/me', [AdminAuthController::class, 'me']);
    Route::post('admin/logout', [AdminAuthController::class, 'logout']);
});

// ---- Feature 1: public reads (catalogue + detailed product page) ----
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/{product}/variants', [ProductVariantController::class, 'index']);
Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
Route::post('products/{product}/reviews', [ReviewController::class, 'store']);

// Trending rail (admin-curated featured shoes).
Route::get('trending', [TrendingController::class, 'index']);

// ---- Feature 2: filter panel options + lookup lists ----
Route::get('filters', [ProductController::class, 'filters']);
Route::get('brands',     fn () => Brand::orderBy('name')->get(['id', 'name', 'slug']));
Route::get('colors',     fn () => Color::orderBy('name')->get(['id', 'name', 'hex_code']));
Route::get('sizes',      fn () => Size::orderBy('sort_order')->get(['id', 'label', 'foot_length_cm']));
Route::get('categories', fn () => Category::with('children')->whereNull('parent_id')->get());

// ---- Feature 3: orders + payments (KHQR / credit card) ----
Route::get('orders', [OrderController::class, 'index']);
Route::post('orders', [OrderController::class, 'store']);
Route::get('orders/{order}', [OrderController::class, 'show']);
Route::post('orders/{order}/pay', [PaymentController::class, 'pay']);
Route::post('payments/{payment}/confirm', [PaymentController::class, 'confirm']);

// ---- Admin writes (catalogue management) — protected ----
Route::middleware('auth:sanctum')->group(function () {
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::patch('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);

    Route::post('products/{product}/variants', [ProductVariantController::class, 'store']);
    Route::put('variants/{variant}', [ProductVariantController::class, 'update']);
    Route::patch('variants/{variant}', [ProductVariantController::class, 'update']);
    Route::delete('variants/{variant}', [ProductVariantController::class, 'destroy']);
});


Route::get('orders/{order}/payments', [OrderController::class, 'payments']);