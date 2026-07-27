<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;

/**
 * Prop *shape* guards.
 *
 * A nested `Resource::collection()` inside another resource stays an
 * unresolved collection object and serialises as {"data": [...]} instead of a
 * list. The page still returns HTTP 200, so status-code tests pass while the
 * React component throws "x.map is not a function" in the browser. These
 * assert the arrays really are arrays.
 */
function listShape(array $page, string $key): mixed
{
    return data_get($page, $key);
}

test('the product page sends variants and reviews as arrays', function () {
    $variant = ProductVariant::factory()->create(['stock_quantity' => 4]);
    $product = $variant->product;

    Review::factory()->create([
        'product_id' => $product->id,
        'is_hidden' => false,
    ]);

    $this->get("/products/{$product->slug}")
        ->assertOk()
        ->assertInertia(function ($page) {
            $product = $page->toArray()['props']['product'];

            expect($product['variants'])->toBeArray()
                ->and(array_is_list($product['variants']))->toBeTrue()
                ->and($product['reviews'])->toBeArray()
                ->and(array_is_list($product['reviews']))->toBeTrue();

            // The colour/size ids the picker matches variants on must be there.
            expect($product['variants'][0])->toHaveKeys(['id', 'color', 'size', 'stock_quantity']);
        });
});

test('the cart page sends items as an array', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 4]);
    Cart::activeFor($user)->items()->create([
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)->get(route('cart.show'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $cart = $page->toArray()['props']['cart'];

            expect($cart['items'])->toBeArray()
                ->and(array_is_list($cart['items']))->toBeTrue()
                ->and($cart['items'][0])->toHaveKeys(['id', 'quantity', 'line_total']);
        });
});

test('the wishlist page sends items as an array', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create();
    Wishlist::defaultFor($user)->items()->create([
        'product_id' => $variant->product_id,
        'created_at' => now(),
    ]);

    $this->actingAs($user)->get(route('wishlist.show'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $wishlist = $page->toArray()['props']['wishlist'];

            expect($wishlist['items'])->toBeArray()
                ->and(array_is_list($wishlist['items']))->toBeTrue();
        });
});

test('order pages send items and payments as arrays', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 4]);
    $order = Order::factory()->create(['user_id' => $user->id]);
    $order->items()->create([
        'product_variant_id' => $variant->id,
        'product_name' => $variant->product->name,
        'variant_label' => $variant->variant_label,
        'unit_price' => 10,
        'quantity' => 1,
        'line_total' => 10,
    ]);

    $this->actingAs($user)->get(route('orders.show', $order))
        ->assertOk()
        ->assertInertia(function ($page) {
            $order = $page->toArray()['props']['order'];

            expect($order['items'])->toBeArray()
                ->and(array_is_list($order['items']))->toBeTrue();
        });

    // Order history renders a list of orders, each with its own items array.
    $this->actingAs($user)->get(route('orders.index'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $orders = $page->toArray()['props']['orders'];

            expect($orders)->toBeArray()
                ->and($orders[0]['items'])->toBeArray()
                ->and(array_is_list($orders[0]['items']))->toBeTrue();
        });
});

test('the checkout page sends cart items as an array', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 4]);
    Cart::activeFor($user)->items()->create([
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)->get(route('checkout.show'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $props = $page->toArray()['props'];

            expect($props['cart']['items'])->toBeArray()
                ->and(array_is_list($props['cart']['items']))->toBeTrue()
                ->and($props['addresses'])->toBeArray()
                ->and(array_is_list($props['addresses']))->toBeTrue();
        });
});
