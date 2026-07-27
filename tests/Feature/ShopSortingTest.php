<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Trending;

/**
 * The storefront "Sort by" dropdown on /men|/women|/kids and /search.
 * Every option maps to an ordering in ShopController.
 */
function shoe(string $name, float $price, Category $category): Product
{
    $product = Product::factory()->create([
        'name' => $name,
        'base_price' => $price,
        'is_active' => true,
    ]);
    $product->categories()->attach($category);

    return $product;
}

/** @return array<int, string> product names in the order the page lists them */
function sortedNames(string $url): array
{
    return array_column(
        test()->get($url)->assertOk()->viewData('page')['props']['products'],
        'name',
    );
}

beforeEach(function () {
    $this->category = Category::factory()->create(['slug' => 'men', 'name' => 'Men']);
});

test('price sorts run cheapest and dearest first', function () {
    shoe('Middle', 100, $this->category);
    shoe('Cheapest', 50, $this->category);
    shoe('Dearest', 200, $this->category);

    expect(sortedNames('/men?sort=price_asc'))->toBe(['Cheapest', 'Middle', 'Dearest'])
        ->and(sortedNames('/men?sort=price_desc'))->toBe(['Dearest', 'Middle', 'Cheapest']);
});

test('top rated sorts by the average of visible reviews', function () {
    $good = shoe('Well Reviewed', 100, $this->category);
    $bad = shoe('Poorly Reviewed', 100, $this->category);

    Review::factory()->create(['product_id' => $good->id, 'rating' => 5, 'is_hidden' => false]);
    Review::factory()->create(['product_id' => $bad->id, 'rating' => 1, 'is_hidden' => false]);

    expect(sortedNames('/men?sort=top_rated'))->toBe(['Well Reviewed', 'Poorly Reviewed']);
});

test('trending sorts featured shoes first, in the order an admin curated', function () {
    $plain = shoe('Not Featured', 100, $this->category);
    $second = shoe('Featured Second', 100, $this->category);
    $first = shoe('Featured First', 100, $this->category);

    Trending::factory()->create(['product_id' => $second->id, 'sort_order' => 2]);
    Trending::factory()->create(['product_id' => $first->id, 'sort_order' => 1]);

    expect(sortedNames('/men?sort=trending'))
        ->toBe(['Featured First', 'Featured Second', 'Not Featured']);

    // The un-featured shoe is still listed, just last.
    expect($plain->fresh())->not->toBeNull();
});

test('a deactivated trending row does not count as trending', function () {
    $product = shoe('Switched Off', 100, $this->category);
    shoe('Plain', 100, $this->category);

    Trending::factory()->inactive()->create(['product_id' => $product->id, 'sort_order' => 1]);

    $products = collect(
        $this->get('/men?sort=trending')->assertOk()->viewData('page')['props']['products']
    )->keyBy('name');

    expect($products['Switched Off']['is_trending'])->toBeFalse()
        ->and($products['Plain']['is_trending'])->toBeFalse();
});

test('the listing tells the card which shoes are trending', function () {
    $featured = shoe('Featured', 100, $this->category);
    shoe('Plain', 100, $this->category);

    Trending::factory()->create(['product_id' => $featured->id, 'sort_order' => 1]);

    $products = collect(
        $this->get('/men')->assertOk()->viewData('page')['props']['products']
    )->keyBy('name');

    expect($products['Featured']['is_trending'])->toBeTrue()
        ->and($products['Plain']['is_trending'])->toBeFalse();
});

test('search offers the same trending sort', function () {
    $featured = shoe('Runner Featured', 100, $this->category);
    shoe('Runner Plain', 100, $this->category);

    Trending::factory()->create(['product_id' => $featured->id, 'sort_order' => 1]);

    expect(sortedNames('/search?q=Runner&sort=trending'))
        ->toBe(['Runner Featured', 'Runner Plain']);
});
