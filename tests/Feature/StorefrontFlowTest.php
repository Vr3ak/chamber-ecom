<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * End-to-end customer journey: register → browse → cart → checkout → pay →
 * confirmation → order history → review. These guard the wiring *between*
 * screens, which unit-level controller tests don't cover.
 */
beforeEach(function () {
    Mail::fake(); // advancing to 'paid' sends the order_confirmed mail
});

test('registering lands the new customer on the storefront, not a dashboard', function () {
    $this->post(route('register.store'), [
        'name' => 'New Customer',
        'email' => 'new@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertRedirect('/');

    $user = User::where('email', 'new@example.com')->sole();

    // A fresh signup is a customer, never an admin.
    expect($user->isAdmin())->toBeFalse()
        ->and($user->isSuspended())->toBeFalse();
});

test('the dashboard route no longer exists', function () {
    $this->actingAs(User::factory()->create())->get('/dashboard')->assertNotFound();
});

test('a customer can go from product page to a paid order', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 5, 'price' => 60]);
    $product = $variant->product;

    // 1. The product page renders for a guest.
    $this->get("/products/{$product->slug}")->assertOk();

    // 2. Add the chosen variant to the cart.
    $this->actingAs($user)
        ->post(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ])
        ->assertRedirect();

    expect(Cart::activeFor($user)->items()->sum('quantity'))->toBe(2);

    // 3. The cart page renders and checkout is reachable.
    $this->actingAs($user)->get(route('cart.show'))->assertOk();
    $this->actingAs($user)->get(route('checkout.show'))->assertOk();

    // 4. Place the order.
    $this->actingAs($user)->post(route('checkout.store'), [
        'shipping_name' => 'New Customer',
        'shipping_phone' => '+855 12 345 678',
        'street_line' => '123 Monivong Blvd',
        'city' => 'Phnom Penh',
        'province' => 'PP',
        'postal_code' => '12000',
        'country' => 'Cambodia',
    ])->assertRedirect();

    $order = Order::where('user_id', $user->id)->sole();

    expect($order->order_number)->toStartWith('CH-')
        ->and((float) $order->total)->toBe(120.0)
        // Stock is decremented and the cart emptied, so a refresh can't reorder.
        ->and($variant->fresh()->stock_quantity)->toBe(3)
        ->and(Cart::activeFor($user)->items()->count())->toBe(0);

    // 5. Pay, then land on the confirmation screen.
    $this->actingAs($user)->get(route('checkout.pay', $order))->assertOk();
    $payment = $order->payments()->where('status', 'pending')->sole();

    $this->actingAs($user)
        ->post(route('checkout.confirm', $payment))
        ->assertRedirect(route('checkout.confirmation', $order));

    $this->actingAs($user)->get(route('checkout.confirmation', $order))->assertOk();

    expect($order->fresh()->status)->toBe('paid');

    // 6. It shows up in history and detail.
    $this->actingAs($user)->get(route('orders.index'))->assertOk();
    $this->actingAs($user)->get(route('orders.show', $order))->assertOk();
});

test('checkout is refused when the cart is empty', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('checkout.show'))
        ->assertRedirect(route('cart.show'));
});

test('checkout will not oversell stock', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 5, 'price' => 10]);

    $cart = Cart::activeFor($user);
    // Written straight to the cart: the add-item endpoint would have blocked
    // this, but stock can also fall between adding and checking out.
    $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 9]);

    $this->actingAs($user)->post(route('checkout.store'), [
        'shipping_name' => 'Over Buyer',
        'shipping_phone' => '012',
        'street_line' => '1 Test St',
        'city' => 'Phnom Penh',
        'country' => 'Cambodia',
    ])->assertSessionHasErrors('cart');

    expect(Order::where('user_id', $user->id)->count())->toBe(0)
        ->and($variant->fresh()->stock_quantity)->toBe(5);
});

test('a customer cannot view or pay for another customer\'s order', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($intruder)->get(route('orders.show', $order))->assertNotFound();
    $this->actingAs($intruder)->get(route('checkout.pay', $order))->assertNotFound();
    $this->actingAs($intruder)->get(route('checkout.confirmation', $order))->assertNotFound();
});

test('a review can be written against an order the customer owns, at any stage', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 3]);
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
    $order->items()->create([
        'product_variant_id' => $variant->id,
        'product_name' => $variant->product->name,
        'variant_label' => $variant->variant_label,
        'unit_price' => 10,
        'quantity' => 1,
        'line_total' => 10,
    ]);

    $payload = ['product_id' => $variant->product_id, 'rating' => 5, 'body' => 'Great fit.'];

    // No need to wait for delivery — a pending order can be reviewed.
    $this->actingAs($user)
        ->post(route('orders.reviews.store', $order), $payload)
        ->assertRedirect();

    $review = Review::where('user_id', $user->id)->sole();
    expect($review->rating)->toBe(5)
        ->and($review->is_verified)->toBeTrue()
        ->and($review->order_id)->toBe($order->id);
});

test('another customer cannot review against an order that is not theirs', function () {
    $order = Order::factory()->create(['status' => 'delivered']);
    $variant = ProductVariant::factory()->create();
    $order->items()->create([
        'product_variant_id' => $variant->id,
        'product_name' => $variant->product->name,
        'variant_label' => $variant->variant_label,
        'unit_price' => 10,
        'quantity' => 1,
        'line_total' => 10,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('orders.reviews.store', $order), [
            'product_id' => $variant->product_id,
            'rating' => 5,
        ])
        ->assertNotFound();

    expect(Review::count())->toBe(0);
});

/**
 * `reviewable` is what decides whether the order page renders a "Write a
 * review" button at all, so the Order History → review path hinges on it.
 */
test('the order page offers a review right away, and not twice for the same shoe', function (string $status) {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 3]);
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => $status]);
    $item = $order->items()->create([
        'product_variant_id' => $variant->id,
        'product_name' => $variant->product->name,
        'variant_label' => $variant->variant_label,
        'unit_price' => 10,
        'quantity' => 1,
        'line_total' => 10,
    ]);

    $reviewable = fn () => $this->actingAs($user)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->viewData('page')['props']['reviewable'];

    // Offered whatever stage the order is at — no wait for delivery.
    expect($reviewable())->toBe([[
        'order_item_id' => $item->id,
        'product_id' => $variant->product_id,
        'product_name' => $variant->product->name,
    ]]);

    $this->actingAs($user)->post(route('orders.reviews.store', $order), [
        'product_id' => $variant->product_id,
        'rating' => 4,
    ])->assertRedirect();

    // One review per product per customer, so the button goes away.
    expect($reviewable())->toBe([]);
})->with(['pending', 'paid', 'shipped', 'delivered']);

test('a customer cannot review a product that is not on their order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'delivered']);
    $unrelated = Product::factory()->create();

    $this->actingAs($user)
        ->post(route('orders.reviews.store', $order), [
            'product_id' => $unrelated->id,
            'rating' => 5,
        ])
        ->assertForbidden();

    expect(Review::count())->toBe(0);
});

test('search finds products by name and brand', function () {
    $product = Product::factory()->create(['name' => 'Air Glide Runner', 'is_active' => true]);

    $this->get('/search?q=Air Glide')->assertOk();
    $this->get('/search?q='.urlencode($product->brand->name))->assertOk();
    $this->get('/search')->assertOk();
});

test('guests are sent to login before cart, wishlist, checkout and orders', function (string $route) {
    $this->get($route)->assertRedirect(route('login'));
})->with([
    '/cart',
    '/wishlist',
    '/checkout',
    '/orders',
]);

test('the cart badge count is shared with every page', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);

    Cart::activeFor($user)->items()->create([
        'product_variant_id' => $variant->id,
        'quantity' => 3,
    ]);

    $this->actingAs($user)->get('/')
        ->assertInertia(fn ($page) => $page->where('cartCount', 3));
});

test('viewing a page does not create an empty cart row', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')->assertOk();

    expect(Cart::where('user_id', $user->id)->count())->toBe(0);
});

test('a paid order still reaches tracking', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
    Payment::factory()->create([
        'order_id' => $order->id,
        'status' => 'succeeded',
        'method' => 'khqr',
    ]);

    $this->actingAs($user)->get(route('checkout.pay', $order))
        ->assertRedirect(route('track', ['order' => $order->order_number]));
});
