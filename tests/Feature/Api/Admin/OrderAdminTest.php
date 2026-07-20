<?php

use App\Models\Admin;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(Admin::factory()->create(), ['*']);
});

test('the admin order list is not scoped to a single customer and reports stats', function () {
    Order::factory()->create(['user_id' => User::factory(), 'status' => 'pending']);
    Order::factory()->create(['user_id' => User::factory(), 'status' => 'delivered']);
    $refundedOrder = Order::factory()->create(['user_id' => User::factory(), 'status' => 'cancelled']);
    Payment::factory()->create(['order_id' => $refundedOrder->id, 'status' => 'refunded']);

    $this->getJson('/api/admin/orders')
        ->assertOk()
        ->assertJsonPath('stats.total', 3)
        ->assertJsonPath('stats.pending', 1)
        ->assertJsonPath('stats.completed', 1)
        ->assertJsonPath('stats.refunded', 1);
});

test('filtering by refunded=1 only returns orders with a refunded payment', function () {
    $refunded = Order::factory()->create();
    Payment::factory()->create(['order_id' => $refunded->id, 'status' => 'refunded']);
    Order::factory()->create(); // not refunded

    $response = $this->getJson('/api/admin/orders?refunded=1')->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->all();
    expect($ids)->toEqual([$refunded->id]);
});

test('the order detail includes the customer panel and their total order count', function () {
    $customer = User::factory()->create();
    Order::factory()->count(3)->create(['user_id' => $customer->id]);
    $order = Order::factory()->create(['user_id' => $customer->id]);

    $response = $this->getJson("/api/admin/orders/{$order->id}")->assertOk();

    expect($response->json('order.customer.id'))->toBe($customer->id)
        ->and($response->json('order.customer.orders_count'))->toBe(4);
});

test('an admin can set the tracking number without changing the order status', function () {
    $order = Order::factory()->create(['status' => 'packed']);

    $this->patchJson("/api/admin/orders/{$order->id}/tracking-number", ['tracking_number' => 'TRK-999'])
        ->assertOk()
        ->assertJsonPath('data.tracking_number', 'TRK-999')
        ->assertJsonPath('data.status', 'packed');
});
