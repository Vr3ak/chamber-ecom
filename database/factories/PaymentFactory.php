<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => 'khqr',
            'status' => 'succeeded',
            'amount' => fake()->randomFloat(2, 20, 300),
            'currency' => 'USD',
            'transaction_ref' => fake()->unique()->bothify('TXN-########'),
            'paid_at' => now(),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status, 'paid_at' => $status === 'succeeded' ? now() : null]);
    }
}
