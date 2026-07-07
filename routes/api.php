<?php

use App\Http\Controllers\Api\AdminAuthController;
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
| Feature 1 — Detailed Product Page + shoe CRUD.
|
| Public reads are open. Catalogue writes require an admin bearer token
| (Sanctum): log in at POST /api/admin/login, then send the token as
| `Authorization: Bearer <token>` on the protected routes below.
*/

// ---- Admin auth ----
Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('admin/me', [AdminAuthController::class, 'me']);
    Route::post('admin/logout', [AdminAuthController::class, 'logout']);
});

// ---- Public reads (catalogue + detailed product page) ----
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/{product}/variants', [ProductVariantController::class, 'index']);
Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
Route::post('products/{product}/reviews', [ReviewController::class, 'store']);

// Trending rail (admin-curated featured shoes).
Route::get('trending', [TrendingController::class, 'index']);

// Lookup lists for the filter sidebar / breadcrumb.
Route::get('brands',     fn () => Brand::orderBy('name')->get(['id', 'name', 'slug']));
Route::get('colors',     fn () => Color::orderBy('name')->get(['id', 'name', 'hex_code']));
Route::get('sizes',      fn () => Size::orderBy('sort_order')->get(['id', 'label', 'foot_length_cm']));
Route::get('categories', fn () => Category::with('children')->whereNull('parent_id')->get());

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