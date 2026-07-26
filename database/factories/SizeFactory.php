<?php

namespace Database\Factories;

use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Size>
 */
class SizeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'label' => (string) fake()->unique()->numberBetween(35, 48),
            'foot_length_cm' => fake()->randomFloat(1, 22, 31),
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
