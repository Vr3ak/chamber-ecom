<?php

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(Admin::factory()->create(), ['*']);
    Storage::fake('public');
});

test('creating a shoe with an image attaches it as the primary product image', function () {
    $brand = Brand::factory()->create();

    $this->post('/api/products', [
        'brand_id' => $brand->id,
        'name' => 'Air Glide Test',
        'base_price' => 59,
        'image' => UploadedFile::fake()->image('shoe.jpg'),
    ])->assertCreated();

    $product = Product::where('name', 'Air Glide Test')->sole();
    $image = $product->images()->sole();

    expect($image->is_primary)->toBeTrue()
        ->and(Storage::disk('public')->allFiles("products/{$product->id}"))->toHaveCount(1);
});

test('adding a gallery image via the dedicated endpoint works and can be deleted', function () {
    $product = Product::factory()->create();

    $response = $this->post("/api/products/{$product->slug}/images", [
        'image' => UploadedFile::fake()->image('extra.jpg'),
        'alt' => 'Side view',
    ])->assertCreated();

    $imageId = $response->json('data.id');
    expect($product->images()->count())->toBe(1);

    $this->deleteJson("/api/product-images/{$imageId}")->assertNoContent();
    expect($product->images()->count())->toBe(0);
});
