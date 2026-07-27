<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Mail::fake(); // advancing to 'paid' sends the order_confirmed mail
});

function makeOrder(User $user, string $status = 'pending', float $total = 120.00): Order
{
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'CH-TEST-'.$user->id,
        'status' => $status,
        'subtotal' => $total,
        'total' => $total,
        'shipping_name' => 'Dara Sok',
        'shipping_phone' => '012345678',
        'shipping_address' => 'St 271, Phnom Penh',
        'placed_at' => now(),
    ]);

    return $order;
}

test('the owner sees the KHQR payment page with a real QR payload', function () {
    $user = User::factory()->create();
    $order = makeOrder($user);

    $this->actingAs($user)
        ->get(route('checkout.pay', $order))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('checkout/payment')
            ->where('order.total', fn ($total) => (float) $total === 120.0)
            ->where('order.order_number', $order->order_number)
            ->has('payment.id')
            // qrSvgDataUri returns an inline SVG data URI
            ->where('qrImage', fn (string $uri) => str_starts_with($uri, 'data:image/svg+xml'))
        );

    // Opening the page leaves exactly one pending KHQR payment to confirm.
    expect($order->payments()->where('status', 'pending')->count())->toBe(1);
});

test('reloading the page reuses the pending payment instead of stacking rows', function () {
    $user = User::factory()->create();
    $order = makeOrder($user);

    $this->actingAs($user)->get(route('checkout.pay', $order))->assertOk();
    $this->actingAs($user)->get(route('checkout.pay', $order))->assertOk();

    expect($order->payments()->count())->toBe(1);
});

test('another customer cannot open someone else\'s payment page', function () {
    $order = makeOrder(User::factory()->create());

    $this->actingAs(User::factory()->create())
        ->get(route('checkout.pay', $order))
        ->assertNotFound();
});

test('guests are sent to login', function () {
    $order = makeOrder(User::factory()->create());

    $this->get(route('checkout.pay', $order))->assertRedirect(route('login'));
});

test('an already paid order goes straight to tracking', function () {
    $user = User::factory()->create();
    $order = makeOrder($user);
    $order->payments()->create([
        'method' => 'khqr', 'status' => 'succeeded',
        'amount' => 120.00, 'currency' => 'USD', 'paid_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('checkout.pay', $order))
        ->assertRedirect(route('track', ['order' => $order->order_number]));
});

test('confirming marks the order paid and logs it on the tracking timeline', function () {
    $user = User::factory()->create();
    $order = makeOrder($user);

    $this->actingAs($user)->get(route('checkout.pay', $order));
    $payment = $order->payments()->where('status', 'pending')->sole();

    $this->actingAs($user)
        ->post(route('checkout.confirm', $payment))
        ->assertRedirect(route('checkout.confirmation', $order));

    expect($payment->fresh()->status)->toBe('succeeded')
        ->and($payment->fresh()->paid_at)->not->toBeNull()
        ->and($order->fresh()->status)->toBe('paid')
        // the regression this guards: a bare status write skipped the timeline
        ->and($order->tracking()->where('status', 'paid')->exists())->toBeTrue();
});

test('a failed result marks the payment failed and leaves the order unpaid', function () {
    $user = User::factory()->create();
    $order = makeOrder($user);

    $this->actingAs($user)->get(route('checkout.pay', $order));
    $payment = $order->payments()->where('status', 'pending')->sole();

    $this->actingAs($user)
        ->post(route('checkout.confirm', $payment), ['result' => 'fail'])
        ->assertSessionHasErrors('payment');

    expect($payment->fresh()->status)->toBe('failed')
        ->and($order->fresh()->status)->toBe('pending');
});

test('a payment cannot be confirmed twice', function () {
    $user = User::factory()->create();
    $order = makeOrder($user);

    $this->actingAs($user)->get(route('checkout.pay', $order));
    $payment = $order->payments()->where('status', 'pending')->sole();

    $this->actingAs($user)->post(route('checkout.confirm', $payment));
    $this->actingAs($user)->post(route('checkout.confirm', $payment))->assertStatus(409);
});

test('another customer cannot confirm someone else\'s payment', function () {
    $user = User::factory()->create();
    $order = makeOrder($user);

    $this->actingAs($user)->get(route('checkout.pay', $order));
    $payment = $order->payments()->where('status', 'pending')->sole();

    $this->actingAs(User::factory()->create())
        ->post(route('checkout.confirm', $payment))
        ->assertNotFound();

    expect($order->fresh()->status)->toBe('pending');
});
