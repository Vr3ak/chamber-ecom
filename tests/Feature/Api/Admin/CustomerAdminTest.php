<?php

use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(Admin::factory()->create(), ['*']);
});

test('the customer list includes stats and each customer\'s order count', function () {
    $customer = User::factory()->create();
    Order::factory()->count(2)->create(['user_id' => $customer->id]);
    User::factory()->count(2)->create(); // other customers, no orders

    $this->getJson('/api/admin/customers')
        ->assertOk()
        ->assertJsonPath('stats.total', 3)
        ->assertJsonPath('stats.active', 3)
        ->assertJsonPath('stats.suspended', 0);

    $response = $this->getJson("/api/admin/customers/{$customer->id}")->assertOk();
    expect($response->json('data.orders_count'))->toBe(2);
});

test('an admin can suspend and then reactivate a customer', function () {
    $customer = User::factory()->create();

    $this->postJson("/api/admin/customers/{$customer->id}/suspend")
        ->assertOk()
        ->assertJsonPath('data.is_suspended', true);

    expect($customer->fresh()->isSuspended())->toBeTrue();

    $this->postJson("/api/admin/customers/{$customer->id}/activate")
        ->assertOk()
        ->assertJsonPath('data.is_suspended', false);

    expect($customer->fresh()->isSuspended())->toBeFalse();
});

test('suspended filter only returns suspended customers', function () {
    $active = User::factory()->create();
    $suspended = User::factory()->create(['is_suspended' => true]);

    $response = $this->getJson('/api/admin/customers?status=suspended')->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($suspended->id)->not->toContain($active->id);
});

test('the customer export streams a CSV with a header row', function () {
    User::factory()->create(['name' => 'Dara Sok']);

    $response = $this->get('/api/admin/customers/export')->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();
    expect($content)->toContain('ID,Name,Email,Phone,Orders,Joined,Status')
        ->and($content)->toContain('Dara Sok');
});
