<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => null,
            'channel' => 'email',
            'type' => 'order_confirmed',
            'recipient' => fake()->safeEmail(),
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed', 'sent_at' => null]);
    }
}
