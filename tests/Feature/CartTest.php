<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

test('guests are sent to login', function () {
    $this->get(route('cart.show'))->assertRedirect(route('login'));
});

test('viewing the cart creates an active cart on first visit', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson(route('cart.show'))
        ->assertOk()
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.items', []);

    expect(Cart::where('user_id', $user->id)->where('status', 'active')->count())->toBe(1);
});

test('adding an item creates a cart item with the correct totals', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 10, 'price' => 25]);

    $response = $this->actingAs($user)
        ->postJson(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 2])
        ->assertOk()
        ->assertJsonPath('data.items.0.quantity', 2)
        ->assertJsonPath('data.items_count', 2);

    expect((float) $response->json('data.items.0.unit_price'))->toBe(25.0)
        ->and((float) $response->json('data.items.0.line_total'))->toBe(50.0)
        ->and((float) $response->json('data.subtotal'))->toBe(50.0);
});

test('adding the same variant twice increments quantity instead of duplicating the row', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)->postJson(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 2]);
    $this->actingAs($user)->postJson(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 3])
        ->assertOk()
        ->assertJsonPath('data.items.0.quantity', 5);

    expect(CartItem::where('product_variant_id', $variant->id)->count())->toBe(1);
});

test('adding by bare product_id resolves an in-stock default variant', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id, 'stock_quantity' => 0]);
    $inStock = ProductVariant::factory()->create(['product_id' => $product->id, 'stock_quantity' => 5]);

    $this->actingAs($user)
        ->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertOk()
        ->assertJsonPath('data.items.0.variant.id', $inStock->id);
});

test('adding by product_id with no variants at all is rejected', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertStatus(422);
});

test('adding more than available stock is rejected', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 2]);

    $this->actingAs($user)
        ->postJson(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 5])
        ->assertJsonValidationErrors('quantity');
});

test('an owner can update and remove their own cart item', function () {
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);
    $cart = Cart::activeFor($user);
    $item = $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 1]);

    $this->actingAs($user)
        ->patchJson(route('cart.items.update', $item), ['quantity' => 4])
        ->assertOk()
        ->assertJsonPath('data.items.0.quantity', 4);

    $this->actingAs($user)->deleteJson(route('cart.items.destroy', $item))
        ->assertOk()
        ->assertJsonPath('data.items', []);
});

test('another customer cannot touch someone else\'s cart item', function () {
    $owner = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);
    $item = Cart::activeFor($owner)->items()->create(['product_variant_id' => $variant->id, 'quantity' => 1]);

    $this->actingAs(User::factory()->create())
        ->patchJson(route('cart.items.update', $item), ['quantity' => 2])
        ->assertNotFound();

    $this->actingAs(User::factory()->create())
        ->deleteJson(route('cart.items.destroy', $item))
        ->assertNotFound();
});

test('clearing the cart removes every item', function () {
    $user = User::factory()->create();
    $cart = Cart::activeFor($user);
    $cart->items()->create(['product_variant_id' => ProductVariant::factory()->create(['stock_quantity' => 5])->id, 'quantity' => 1]);
    $cart->items()->create(['product_variant_id' => ProductVariant::factory()->create(['stock_quantity' => 5])->id, 'quantity' => 1]);

    $this->actingAs($user)->deleteJson(route('cart.clear'))
        ->assertOk()
        ->assertJsonPath('data.items', []);

    expect($cart->items()->count())->toBe(0);
});
