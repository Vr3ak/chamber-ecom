<?php

// use App\Http\Controllers\ProductController;

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\CatalogAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\PeopleAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutPaymentController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
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

// Storefront: search, category listing (/men /women /kids) + product detail.
// `search` is declared before the {gender} catch-all so it wins the match.
Route::get('search', [ShopController::class, 'search'])->name('shop.search');
Route::get('products/{product}', [ShopController::class, 'product'])->name('products.show');
Route::get('{gender}', [ShopController::class, 'category'])
    ->whereIn('gender', ['men', 'women', 'kids'])
    ->name('shop.category');

// KHQR payment screen (Feature 3). Owner-scoped: the order comes from the
// route and is checked against the session user, never from request input.
Route::middleware('auth')->group(function () {
    Route::get('orders/{order}/pay', [CheckoutPaymentController::class, 'show'])->name('checkout.pay');
    Route::post('payments/{payment}/confirm', [CheckoutPaymentController::class, 'confirm'])->name('checkout.confirm');

    // Checkout (Figma 03 — Shopping Flow). The order is built from the
    // session user's own cart; see CheckoutController for why this doesn't
    // reuse the request-body-driven Api\OrderController::store.
    Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('orders/{order}/confirmation', [CheckoutController::class, 'confirmation'])
        ->name('checkout.confirmation');

    // Customer order history + detail (Figma 04 — Account & Order History).
    Route::get('orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/reviews', [CustomerOrderController::class, 'review'])
        ->name('orders.reviews.store');
});

// Cart, wishlist, and address book (all JSON, session-owner-scoped —
// never accept a user_id from the request, same rule as checkout above).
Route::middleware('auth')->group(function () {
    Route::get('cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('cart/items', [CartController::class, 'addItem'])->name('cart.items.store');
    Route::patch('cart/items/{cartItem}', [CartController::class, 'updateItem'])->name('cart.items.update');
    Route::delete('cart/items/{cartItem}', [CartController::class, 'removeItem'])->name('cart.items.destroy');
    Route::delete('cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('wishlist', [WishlistController::class, 'show'])->name('wishlist.show');
    Route::post('wishlist/items', [WishlistController::class, 'addItem'])->name('wishlist.items.store');
    Route::delete('wishlist/items/{wishlistItem}', [WishlistController::class, 'removeItem'])->name('wishlist.items.destroy');

    Route::get('addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('addresses/{address}/default', [AddressController::class, 'makeDefault'])->name('addresses.default');
});

/*
|--------------------------------------------------------------------------
| Admin panel (Figma sections 05–09)
|--------------------------------------------------------------------------
| Session-authenticated and gated on users.is_admin — see EnsureAdminSession
| for why the Sanctum-based 'admin' alias can't be used here.
*/
Route::middleware(['auth', 'admin.session'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Products + variants
    Route::get('products', [ProductAdminController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductAdminController::class, 'create'])->name('products.create');
    Route::post('products', [ProductAdminController::class, 'store'])->name('products.store');
    Route::get('products/{product:id}/edit', [ProductAdminController::class, 'edit'])->name('products.edit');
    Route::put('products/{product:id}', [ProductAdminController::class, 'update'])->name('products.update');
    Route::delete('products/{product:id}', [ProductAdminController::class, 'destroy'])->name('products.destroy');
    Route::get('products/{product:id}/variants', [ProductAdminController::class, 'variants'])->name('products.variants');
    Route::post('products/{product:id}/variants', [ProductAdminController::class, 'storeVariant'])->name('variants.store');
    Route::patch('variants/{variant}', [ProductAdminController::class, 'updateVariant'])->name('variants.update');
    Route::delete('variants/{variant}', [ProductAdminController::class, 'destroyVariant'])->name('variants.destroy');

    // Orders
    Route::get('orders', [OrderAdminController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderAdminController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/advance', [OrderAdminController::class, 'advance'])->name('orders.advance');
    Route::patch('orders/{order}/tracking-number', [OrderAdminController::class, 'updateTrackingNumber'])
        ->name('orders.tracking');

    // Catalog lookups
    Route::get('brands', [CatalogAdminController::class, 'brands'])->name('brands.index');
    Route::post('brands', [CatalogAdminController::class, 'storeBrand'])->name('brands.store');
    Route::patch('brands/{brand}', [CatalogAdminController::class, 'updateBrand'])->name('brands.update');
    Route::delete('brands/{brand}', [CatalogAdminController::class, 'destroyBrand'])->name('brands.destroy');

    Route::get('categories', [CatalogAdminController::class, 'categories'])->name('categories.index');
    Route::post('categories', [CatalogAdminController::class, 'storeCategory'])->name('categories.store');
    Route::patch('categories/{category:id}', [CatalogAdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('categories/{category:id}', [CatalogAdminController::class, 'destroyCategory'])->name('categories.destroy');

    Route::get('attributes', [CatalogAdminController::class, 'attributes'])->name('attributes.index');
    Route::post('colors', [CatalogAdminController::class, 'storeColor'])->name('colors.store');
    Route::delete('colors/{color}', [CatalogAdminController::class, 'destroyColor'])->name('colors.destroy');
    Route::post('sizes', [CatalogAdminController::class, 'storeSize'])->name('sizes.store');
    Route::delete('sizes/{size}', [CatalogAdminController::class, 'destroySize'])->name('sizes.destroy');

    // Customers, notifications, reviews
    Route::get('customers', [PeopleAdminController::class, 'customers'])->name('customers.index');
    Route::post('customers/{user}/suspend', [PeopleAdminController::class, 'suspendCustomer'])->name('customers.suspend');
    Route::post('customers/{user}/activate', [PeopleAdminController::class, 'activateCustomer'])->name('customers.activate');

    Route::get('notifications', [PeopleAdminController::class, 'notifications'])->name('notifications.index');
    Route::post('notifications/{notification}/resend', [PeopleAdminController::class, 'resendNotification'])
        ->name('notifications.resend');

    Route::get('reviews', [PeopleAdminController::class, 'reviews'])->name('reviews.index');
    Route::post('reviews/{review}/hide', [PeopleAdminController::class, 'hideReview'])->name('reviews.hide');
    Route::post('reviews/{review}/unhide', [PeopleAdminController::class, 'unhideReview'])->name('reviews.unhide');
});

require __DIR__.'/settings.php';
// Route::get('/products', [ProductController::class, 'index'])->name('products.index');
// Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
