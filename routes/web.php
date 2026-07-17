<?php

// use App\Http\Controllers\ProductController;

use App\Http\Controllers\CheckoutPaymentController;
use App\Http\Controllers\ShopController;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderTrackingResource;
use App\Http\Resources\ProductListResource;
use App\Models\Order;
use App\Models\Trending;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $trending = Trending::query()
        ->active()
        ->with(['product' => fn ($q) => $q->active()
            ->with(['brand', 'variants'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')])
        ->get()
        ->pluck('product')
        ->filter()
        ->values();

    return inertia('welcome', [
        'trending' => ProductListResource::collection($trending)->resolve(),
    ]);
})->name('home');

// Public "track your order" page — look up an order by its number and show
// its fulfilment timeline (Feature 4).
Route::get('track', function (Request $request) {
    $number = trim((string) $request->query('order', ''));

    $order = $number === '' ? null : Order::query()
        ->with(['items', 'tracking', 'notifications'])
        ->where('order_number', $number)
        ->first();

    return inertia('track', [
        'query' => $number,
        'notFound' => $number !== '' && ! $order,
        'order' => $order ? OrderResource::make($order)->resolve() : null,
        'timeline' => $order ? OrderTrackingResource::collection($order->trackingTimeline())->resolve() : [],
    ]);
})->name('track');

// Storefront: category listing (/men /women /kids) + product detail page.
Route::get('products/{product}', [ShopController::class, 'product'])->name('products.show');
Route::get('{gender}', [ShopController::class, 'category'])
    ->whereIn('gender', ['men', 'women', 'kids'])
    ->name('shop.category');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

// KHQR payment screen (Feature 3). Owner-scoped: the order comes from the
// route and is checked against the session user, never from request input.
Route::middleware('auth')->group(function () {
    Route::get('orders/{order}/pay', [CheckoutPaymentController::class, 'show'])->name('checkout.pay');
    Route::post('payments/{payment}/confirm', [CheckoutPaymentController::class, 'confirm'])->name('checkout.confirm');
});

require __DIR__.'/settings.php';
// Route::get('/products', [ProductController::class, 'index'])->name('products.index');
// Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
