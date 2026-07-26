<?php

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(Admin::factory()->create(), ['*']);
});

// ---- Brands ----

test('an admin can create, update, and delete a brand', function () {
    $this->postJson('/api/brands', ['name' => 'Acme Sneakers'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Acme Sneakers')
        ->assertJsonPath('data.is_active', true);

    $brand = Brand::where('name', 'Acme Sneakers')->sole();

    $this->putJson("/api/brands/{$brand->id}", ['is_active' => false])
        ->assertOk()
        ->assertJsonPath('data.is_active', false);

    $this->deleteJson("/api/brands/{$brand->id}")->assertNoContent();
    expect(Brand::find($brand->id))->toBeNull();
});

test('creating a brand requires a unique name', function () {
    Brand::factory()->create(['name' => 'Taken']);

    $this->postJson('/api/brands', ['name' => 'Taken'])->assertJsonValidationErrors('name');
});

// ---- Categories ----

test('an admin can create, update, and delete a category', function () {
    $parent = Category::factory()->create();

    $this->postJson('/api/categories', ['name' => 'Trail Running', 'parent_id' => $parent->id])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Trail Running');

    $category = Category::where('name', 'Trail Running')->sole();

    $this->putJson("/api/categories/{$category->slug}", ['name' => 'Trail Runs'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Trail Runs');

    $this->deleteJson("/api/categories/{$category->slug}")->assertNoContent();
});

test('a category cannot be made its own parent', function () {
    $category = Category::factory()->create();

    $this->putJson("/api/categories/{$category->slug}", ['parent_id' => $category->id])
        ->assertJsonValidationErrors('parent_id');
});

// ---- Colors ----

test('an admin can create, update, and delete a color', function () {
    $this->postJson('/api/colors', ['name' => 'Chartreuse', 'hex_code' => '#7FFF00'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Chartreuse');

    $color = Color::where('name', 'Chartreuse')->sole();

    $this->putJson("/api/colors/{$color->id}", ['hex_code' => '#00FF7F'])
        ->assertOk()
        ->assertJsonPath('data.hex_code', '#00FF7F');

    $this->deleteJson("/api/colors/{$color->id}")->assertNoContent();
});

test('a color hex code must be a valid 6-digit hex value', function () {
    $this->postJson('/api/colors', ['name' => 'Bad', 'hex_code' => 'not-a-color'])
        ->assertJsonValidationErrors('hex_code');
});

// ---- Sizes ----

test('an admin can create, update, and delete a size', function () {
    $this->postJson('/api/sizes', ['label' => '46', 'foot_length_cm' => 30.5])
        ->assertCreated()
        ->assertJsonPath('data.label', '46');

    $size = Size::where('label', '46')->sole();

    $this->putJson("/api/sizes/{$size->id}", ['sort_order' => 99])
        ->assertOk()
        ->assertJsonPath('data.sort_order', 99);

    $this->deleteJson("/api/sizes/{$size->id}")->assertNoContent();
});
