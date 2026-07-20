<?php

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(Admin::factory()->create(), ['*']);
});

test('the dashboard reports totals, revenue, and the order status breakdown', function () {
    [$customerA, $customerB] = User::factory()->count(2)->create();

    $delivered = Order::factory()->create(['status' => 'delivered', 'user_id' => $customerA->id]);
    Payment::factory()->create(['order_id' => $delivered->id, 'status' => 'succeeded', 'amount' => 100, 'paid_at' => now()]);

    $pending = Order::factory()->create(['status' => 'pending', 'user_id' => $customerB->id]);
    Payment::factory()->create(['order_id' => $pending->id, 'status' => 'pending', 'amount' => 50]);

    $shipped = Order::factory()->create(['status' => 'shipped', 'user_id' => $customerA->id]);
    $cancelled = Order::factory()->create(['status' => 'cancelled', 'user_id' => $customerB->id]);

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock_quantity' => 5]);
    OrderItem::factory()->create([
        'order_id' => $delivered->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
        'line_total' => 100,
    ]);

    $response = $this->getJson('/api/admin/dashboard')->assertOk();

    expect((float) $response->json('total_revenue'))->toBe(100.0)
        ->and($response->json('total_orders'))->toBe(4)
        ->and($response->json('total_customers'))->toBe(2)
        ->and($response->json('order_status.delivered'))->toBe(1)
        ->and($response->json('order_status.shipped'))->toBe(1)
        ->and($response->json('order_status.processing'))->toBe(1)
        ->and($response->json('order_status.cancelled'))->toBe(1)
        ->and($response->json('top_products.0.product_id'))->toBe($product->id)
        ->and($response->json('top_products.0.units_sold'))->toBe(3);
});

test('low_stock_count only counts products whose total stock falls in the low-stock band', function () {
    $inStock = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $inStock->id, 'stock_quantity' => 50]);

    $lowStock = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $lowStock->id, 'stock_quantity' => 5]);

    $outOfStock = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $outOfStock->id, 'stock_quantity' => 0]);

    $response = $this->getJson('/api/admin/dashboard')->assertOk();

    expect($response->json('low_stock_count'))->toBe(1);
});
