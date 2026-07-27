<?php

use App\Models\Brand;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;

/** An administrator: users.is_admin, the browser-session side of AdminSeeder. */
function admin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['is_admin' => true])->save();

    return $user->fresh();
}

test('guests are sent to login', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

test('a signed-in customer cannot reach the admin panel', function () {
    $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
});

test('a suspended administrator is locked out', function () {
    $user = admin();
    $user->forceFill(['is_suspended' => true])->save();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

test('every admin screen renders for an administrator', function (string $path) {
    $this->actingAs(admin())->get($path)->assertOk();
})->with([
    '/admin',
    '/admin/products',
    '/admin/products/create',
    '/admin/orders',
    '/admin/brands',
    '/admin/categories',
    '/admin/attributes',
    '/admin/customers',
    '/admin/notifications',
    '/admin/reviews',
]);

test('the product edit and variant screens render', function () {
    $product = Product::factory()->create();

    $this->actingAs(admin())->get("/admin/products/{$product->id}/edit")->assertOk();
    $this->actingAs(admin())->get("/admin/products/{$product->id}/variants")->assertOk();
});

test('the order detail screen renders', function () {
    $order = Order::factory()->create();

    $this->actingAs(admin())->get("/admin/orders/{$order->id}")->assertOk();
});

test('an administrator can create and delete a brand', function () {
    $this->actingAs(admin())
        ->post('/admin/brands', ['name' => 'Trailhead Gear', 'is_active' => true])
        ->assertRedirect();

    $brand = Brand::where('name', 'Trailhead Gear')->sole();
    expect($brand->slug)->toBe('trailhead-gear');

    $this->actingAs(admin())->delete("/admin/brands/{$brand->id}")->assertRedirect();
    expect(Brand::find($brand->id))->toBeNull();
});

test('a brand that still has products is not deleted', function () {
    $product = Product::factory()->create();

    $this->actingAs(admin())
        ->delete("/admin/brands/{$product->brand_id}")
        ->assertSessionHasErrors('brand');

    expect(Brand::find($product->brand_id))->not->toBeNull();
});

test('a duplicate colour and size combination is rejected', function () {
    $product = Product::factory()->create();
    $color = Color::factory()->create();
    $size = Size::factory()->create();

    $payload = [
        'color_id' => $color->id,
        'size_id' => $size->id,
        'stock_quantity' => 5,
    ];

    $this->actingAs(admin())
        ->post("/admin/products/{$product->id}/variants", $payload)
        ->assertRedirect();

    // The second one collides with the uq_variant_combo unique index; it must
    // come back as a field error rather than a database-level 500.
    $this->actingAs(admin())
        ->post("/admin/products/{$product->id}/variants", $payload)
        ->assertSessionHasErrors('color_id');

    expect($product->variants()->count())->toBe(1);
});

test('a customer can be suspended and reactivated', function () {
    $customer = User::factory()->create();

    $this->actingAs(admin())->post("/admin/customers/{$customer->id}/suspend");
    expect($customer->fresh()->isSuspended())->toBeTrue();

    $this->actingAs(admin())->post("/admin/customers/{$customer->id}/activate");
    expect($customer->fresh()->isSuspended())->toBeFalse();
});
