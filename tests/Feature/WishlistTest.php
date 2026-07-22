<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;

test('guests are sent to login', function () {
    $this->get(route('wishlist.show'))->assertRedirect(route('login'));
});

test('viewing the wishlist creates a default wishlist on first visit', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson(route('wishlist.show'))
        ->assertOk()
        ->assertJsonPath('data.name', 'My Wishlist')
        ->assertJsonPath('data.items', []);

    expect(Wishlist::where('user_id', $user->id)->count())->toBe(1);
});

test('adding a product saves it to the wishlist', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->postJson(route('wishlist.items.store'), ['product_id' => $product->id])
        ->assertOk()
        ->assertJsonPath('data.items.0.product.id', $product->id);
});

test('adding the same product twice does not create a duplicate row', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)->postJson(route('wishlist.items.store'), ['product_id' => $product->id]);
    $this->actingAs($user)->postJson(route('wishlist.items.store'), ['product_id' => $product->id])
        ->assertOk()
        ->assertJsonCount(1, 'data.items');

    expect(WishlistItem::where('product_id', $product->id)->count())->toBe(1);
});

test('an owner can remove their own wishlist item', function () {
    $user = User::factory()->create();
    $item = Wishlist::defaultFor($user)->items()->create(['product_id' => Product::factory()->create()->id, 'created_at' => now()]);

    $this->actingAs($user)->deleteJson(route('wishlist.items.destroy', $item))
        ->assertOk()
        ->assertJsonPath('data.items', []);
});

test('another customer cannot remove someone else\'s wishlist item', function () {
    $owner = User::factory()->create();
    $item = Wishlist::defaultFor($owner)->items()->create(['product_id' => Product::factory()->create()->id, 'created_at' => now()]);

    $this->actingAs(User::factory()->create())
        ->deleteJson(route('wishlist.items.destroy', $item))
        ->assertNotFound();
});
