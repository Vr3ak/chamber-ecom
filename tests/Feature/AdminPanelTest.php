<?php

use App\Models\Brand;
use App\Models\Color;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Size;
use App\Models\Trending;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('an administrator can upload a shoe image when creating a product', function () {
    Storage::fake('public');
    $brand = Brand::factory()->create();

    $this->actingAs(admin())
        ->post('/admin/products', [
            'brand_id' => $brand->id,
            'name' => 'Air Glide Test',
            'slug' => 'air-glide-test',
            'base_price' => 59,
            'is_active' => true,
            'image' => UploadedFile::fake()->image('shoe.jpg'),
        ])
        ->assertRedirect();

    $product = Product::where('name', 'Air Glide Test')->sole();
    $image = $product->images()->sole();

    expect($image->is_primary)->toBeTrue()
        ->and(Storage::disk('public')->allFiles("products/{$product->id}"))->toHaveCount(1);
});

test('an administrator can replace a shoe image when editing a product', function () {
    Storage::fake('public');
    $product = Product::factory()->create();

    $this->actingAs(admin())
        ->put("/admin/products/{$product->id}", [
            'brand_id' => $product->brand_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'base_price' => $product->base_price,
            'is_active' => true,
            'image' => UploadedFile::fake()->image('new-shoe.jpg'),
        ])
        ->assertRedirect();

    expect($product->images()->where('is_primary', true)->count())->toBe(1);
});

/**
 * The notifications table has no timestamps, so ordering it with latest()
 * asks for a created_at that does not exist — a 500 on MySQL. SQLite quietly
 * reads the unknown quoted identifier as a string literal, so a status-code
 * check alone passes either way; assert the real ordering instead.
 */
test('the notifications screen lists the rows from the table, newest first', function () {
    $order = Order::factory()->create();
    $customer = User::factory()->create();

    $notifications = collect(['order_placed', 'order_shipped', 'order_delivered'])
        ->map(fn (string $type) => Notification::factory()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'type' => $type,
        ]));

    $this->actingAs(admin())
        ->get('/admin/notifications')
        ->assertOk()
        ->assertInertia(function ($page) use ($notifications, $order, $customer) {
            $rows = $page->toArray()['props']['notifications'];

            expect($rows)->toHaveCount(3)
                ->and(array_column($rows, 'id'))
                ->toBe($notifications->pluck('id')->sortDesc()->values()->all());

            // The row carries the joined order/customer the table renders.
            expect($rows[0])->toMatchArray([
                'type' => 'order_delivered',
                'channel' => 'email',
                'status' => 'sent',
                'order_number' => $order->order_number,
                'customer_name' => $customer->name,
            ]);
        });
});

test('the notifications screen can be filtered by status', function () {
    Notification::factory()->create();
    $failed = Notification::factory()->failed()->create();

    $this->actingAs(admin())
        ->get('/admin/notifications?status=failed')
        ->assertOk()
        ->assertInertia(function ($page) use ($failed) {
            $props = $page->toArray()['props'];

            expect(array_column($props['notifications'], 'id'))->toBe([$failed->id])
                ->and($props['failed_count'])->toBe(1);
        });
});

/** Payload the product form posts; trending rides along with the rest. */
function productPayload(Product $product, array $overrides = []): array
{
    return [
        'brand_id' => $product->brand_id,
        'name' => $product->name,
        'slug' => $product->slug,
        'base_price' => $product->base_price,
        'is_active' => true,
        ...$overrides,
    ];
}

test('an administrator can mark a shoe as trending from the product form', function () {
    $product = Product::factory()->create();

    $this->actingAs(admin())
        ->put("/admin/products/{$product->id}", productPayload($product, ['is_trending' => true]))
        ->assertRedirect();

    expect($product->fresh()->is_trending)->toBeTrue();
});

test('un-marking a shoe keeps its curated position for next time', function () {
    $product = Product::factory()->create();
    Trending::factory()->create(['product_id' => $product->id, 'sort_order' => 7]);

    $this->actingAs(admin())
        ->put("/admin/products/{$product->id}", productPayload($product, ['is_trending' => false]))
        ->assertRedirect();

    expect($product->fresh()->is_trending)->toBeFalse()
        // Flipped, not deleted — so re-featuring restores position 7.
        ->and($product->trending()->sole()->sort_order)->toBe(7);

    $this->actingAs(admin())
        ->put("/admin/products/{$product->id}", productPayload($product, ['is_trending' => true]))
        ->assertRedirect();

    expect($product->fresh()->is_trending)->toBeTrue()
        ->and($product->trending()->sole()->sort_order)->toBe(7);
});

test('a newly trending shoe goes to the back of the rail', function () {
    Trending::factory()->create(['sort_order' => 12]);
    $product = Product::factory()->create();

    $this->actingAs(admin())
        ->put("/admin/products/{$product->id}", productPayload($product, ['is_trending' => true]))
        ->assertRedirect();

    expect($product->trending()->sole()->sort_order)->toBe(13);
});

test('marking trending works for an admin with no row in the admins table', function () {
    $product = Product::factory()->create();

    // The panel authenticates a User; admin_id references the separate
    // `admins` table, so it has to tolerate having nobody to attribute to.
    $this->actingAs(admin())
        ->put("/admin/products/{$product->id}", productPayload($product, ['is_trending' => true]))
        ->assertRedirect();

    expect($product->fresh()->is_trending)->toBeTrue()
        ->and($product->trending()->sole()->admin_id)->toBeNull();
});

test('the product list flags which shoes are trending', function () {
    $featured = Product::factory()->create();
    $plain = Product::factory()->create();
    Trending::factory()->create(['product_id' => $featured->id]);

    $this->actingAs(admin())
        ->get('/admin/products')
        ->assertOk()
        ->assertInertia(function ($page) use ($featured, $plain) {
            $rows = collect($page->toArray()['props']['products'])->keyBy('id');

            expect($rows[$featured->id]['is_trending'])->toBeTrue()
                ->and($rows[$plain->id]['is_trending'])->toBeFalse();
        });
});

test('the reviews screen lists the rows from the table with their product and author', function () {
    $review = Review::factory()->create(['rating' => 4, 'is_hidden' => false]);

    $this->actingAs(admin())
        ->get('/admin/reviews')
        ->assertOk()
        ->assertInertia(function ($page) use ($review) {
            $rows = $page->toArray()['props']['reviews'];

            expect($rows)->toHaveCount(1)
                ->and($rows[0])->toMatchArray([
                    'id' => $review->id,
                    'rating' => 4,
                    'is_hidden' => false,
                    'author' => $review->user->name,
                    'product' => $review->product->name,
                    'product_slug' => $review->product->slug,
                ]);
        });
});

test('the reviews screen can be filtered by visibility', function () {
    $visible = Review::factory()->create(['is_hidden' => false]);
    $hidden = Review::factory()->create(['is_hidden' => true]);

    $ids = fn (string $query) => array_column(
        $this->actingAs(admin())->get("/admin/reviews{$query}")->assertOk()
            ->viewData('page')['props']['reviews'],
        'id',
    );

    expect($ids('?visibility=hidden'))->toBe([$hidden->id])
        ->and($ids('?visibility=visible'))->toBe([$visible->id])
        ->and($ids(''))->toHaveCount(2);
});

test('an administrator can hide a review and restore it', function () {
    $review = Review::factory()->create(['is_hidden' => false]);

    $this->actingAs(admin())->post("/admin/reviews/{$review->id}/hide")->assertRedirect();
    expect($review->fresh()->is_hidden)->toBeTrue();

    $this->actingAs(admin())->post("/admin/reviews/{$review->id}/unhide")->assertRedirect();
    expect($review->fresh()->is_hidden)->toBeFalse();
});

test('a customer can be suspended and reactivated', function () {
    $customer = User::factory()->create();

    $this->actingAs(admin())->post("/admin/customers/{$customer->id}/suspend");
    expect($customer->fresh()->isSuspended())->toBeTrue();

    $this->actingAs(admin())->post("/admin/customers/{$customer->id}/activate");
    expect($customer->fresh()->isSuspended())->toBeFalse();
});
