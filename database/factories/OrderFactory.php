<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'CH-'.fake()->unique()->numerify('####-####'),
            'status' => 'pending',
            'subtotal' => 0,
            'total' => 0,
            'shipping_name' => fake()->name(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->address(),
            'placed_at' => now(),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
