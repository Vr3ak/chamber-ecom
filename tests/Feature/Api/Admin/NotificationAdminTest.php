<?php

use App\Models\Admin;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(Admin::factory()->create(), ['*']);
    Mail::fake();
});

test('the notifications list includes every status, not just failed', function () {
    $order = Order::factory()->create();
    Notification::factory()->create(['order_id' => $order->id, 'status' => 'sent']);
    Notification::factory()->failed()->create(['order_id' => $order->id]);

    $response = $this->getJson('/api/admin/notifications')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

test('the legacy failed-only endpoint still only returns failed notifications', function () {
    $order = Order::factory()->create();
    Notification::factory()->create(['order_id' => $order->id, 'status' => 'sent']);
    $failed = Notification::factory()->failed()->create(['order_id' => $order->id]);

    $response = $this->getJson('/api/admin/notifications/failed')->assertOk();

    expect(collect($response->json('data'))->pluck('id')->all())->toEqual([$failed->id]);
});

test('resending creates a fresh notification row and attempts to send again', function () {
    $order = Order::factory()->create();
    $original = Notification::factory()->failed()->create([
        'order_id' => $order->id,
        'type' => 'order_confirmed',
    ]);

    $this->postJson("/api/admin/notifications/{$original->id}/resend")
        ->assertCreated()
        ->assertJsonPath('data.order_number', $order->order_number);

    expect(Notification::where('order_id', $order->id)->count())->toBe(2);
});
