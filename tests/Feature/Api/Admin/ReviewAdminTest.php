<?php

use App\Models\Admin;
use App\Models\Product;
use App\Models\Review;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(Admin::factory()->create(), ['*']);
});

test('the admin review list spans every product', function () {
    Review::factory()->count(2)->create(['product_id' => Product::factory()]);
    Review::factory()->count(1)->create(['product_id' => Product::factory()]);

    $this->getJson('/api/admin/reviews')->assertOk()->assertJsonCount(3, 'data');
});

test('hiding a review removes it from the public product listing and rating average', function () {
    $product = Product::factory()->create();
    Review::factory()->create(['product_id' => $product->id, 'rating' => 5]);
    $toHide = Review::factory()->create(['product_id' => $product->id, 'rating' => 1]);

    $this->getJson("/api/products/{$product->slug}/reviews")->assertOk()->assertJsonCount(2, 'data');

    $this->postJson("/api/admin/reviews/{$toHide->id}/hide")
        ->assertOk()
        ->assertJsonPath('data.is_hidden', true);

    $this->getJson("/api/products/{$product->slug}/reviews")->assertOk()->assertJsonCount(1, 'data');

    $show = $this->getJson("/api/products/{$product->slug}")->assertOk();
    expect($show->json('data.rating.count'))->toBe(1)
        ->and((float) $show->json('data.rating.average'))->toBe(5.0);
});

test('unhiding a review brings it back', function () {
    $review = Review::factory()->create(['is_hidden' => true]);

    $this->postJson("/api/admin/reviews/{$review->id}/unhide")
        ->assertOk()
        ->assertJsonPath('data.is_hidden', false);
});
