<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'recipient_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'street_line' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => null,
            'country' => 'Cambodia',
            'is_default' => false,
        ];
    }
}
