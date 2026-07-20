<?php

use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\TrendingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Chamber API routes  (prefixed with /api by the framework)
|--------------------------------------------------------------------------
| Feature 1 — Detailed product page (+ shoe CRUD)
| Feature 2 — Filter / search
| Feature 3 — Orders + payments (KHQR / credit card)
|
| Public reads are open. Everything else needs an admin Sanctum token —
| enforced by 'auth:sanctum' + the 'admin' alias (App\Http\Middleware\EnsureAdmin).
*/

// ---- Admin auth ----
Route::post('admin/login', [AdminAuthController::class, 'login']);

// ---- Admin-only surfaces (dashboard, customers, order management, notifications inbox, review moderation) ----
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('me', [AdminAuthController::class, 'me']);
    Route::post('logout', [AdminAuthController::class, 'logout']);

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::get('customers', [CustomerController::class, 'index']);
    Route::get('customers/export', [CustomerController::class, 'export']);
    Route::get('customers/{user}', [CustomerController::class, 'show']);
    Route::post('customers/{user}/suspend', [CustomerController::class, 'suspend']);
    Route::post('customers/{user}/activate', [CustomerController::class, 'activate']);

    Route::get('orders', [AdminOrderController::class, 'index']);
    Route::get('orders/{order}', [AdminOrderController::class, 'show']);
    Route::post('orders/{order}/advance', [OrderTrackingController::class, 'advance']);
    Route::patch('orders/{order}/tracking-number', [AdminOrderController::class, 'updateTrackingNumber']);

    Route::get('notifications', [AdminNotificationController::class, 'index']);
    Route::get('notifications/failed', [AdminNotificationController::class, 'failed']);
    Route::post('notifications/{notification}/resend', [AdminNotificationController::class, 'resend']);

    Route::get('reviews', [AdminReviewController::class, 'index']);
    Route::post('reviews/{review}/hide', [AdminReviewController::class, 'hide']);
    Route::post('reviews/{review}/unhide', [AdminReviewController::class, 'unhide']);
});

// ---- Feature 1: public reads (catalogue + detailed product page) ----
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/{product}/variants', [ProductVariantController::class, 'index']);
Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
Route::post('products/{product}/reviews', [ReviewController::class, 'store']);

// Trending rail (admin-curated featured shoes).
Route::get('trending', [TrendingController::class, 'index']);

// ---- Feature 2: filter panel options + lookup lists (shared: storefront facets + admin catalogue tables) ----
Route::get('filters', [ProductController::class, 'filters']);
Route::get('brands', [BrandController::class, 'index']);
Route::get('colors', [ColorController::class, 'index']);
Route::get('sizes', [SizeController::class, 'index']);
Route::get('categories', [CategoryController::class, 'index']);

// ---- Feature 3: orders + payments (KHQR) ----
Route::get('orders', [OrderController::class, 'index']);
Route::post('orders', [OrderController::class, 'store']);
Route::get('orders/{order}', [OrderController::class, 'show']);
Route::post('orders/{order}/pay', [PaymentController::class, 'pay']);
Route::post('payments/{payment}/confirm', [PaymentController::class, 'confirm']);
Route::get('orders/{order}/tracking', [OrderTrackingController::class, 'timeline']);
Route::get('orders/{order}/payments', [OrderController::class, 'payments']);

// ---- Admin writes (catalogue management) — protected ----
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::patch('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);

    Route::post('products/{product}/variants', [ProductVariantController::class, 'store']);
    Route::put('variants/{variant}', [ProductVariantController::class, 'update']);
    Route::patch('variants/{variant}', [ProductVariantController::class, 'update']);
    Route::delete('variants/{variant}', [ProductVariantController::class, 'destroy']);

    Route::post('products/{product}/images', [ProductImageController::class, 'store']);
    Route::delete('product-images/{productImage}', [ProductImageController::class, 'destroy']);

    Route::post('brands', [BrandController::class, 'store']);
    Route::put('brands/{brand}', [BrandController::class, 'update']);
    Route::patch('brands/{brand}', [BrandController::class, 'update']);
    Route::delete('brands/{brand}', [BrandController::class, 'destroy']);

    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category}', [CategoryController::class, 'update']);
    Route::patch('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('colors', [ColorController::class, 'store']);
    Route::put('colors/{color}', [ColorController::class, 'update']);
    Route::patch('colors/{color}', [ColorController::class, 'update']);
    Route::delete('colors/{color}', [ColorController::class, 'destroy']);

    Route::post('sizes', [SizeController::class, 'store']);
    Route::put('sizes/{size}', [SizeController::class, 'update']);
    Route::patch('sizes/{size}', [SizeController::class, 'update']);
    Route::delete('sizes/{size}', [SizeController::class, 'destroy']);
});
