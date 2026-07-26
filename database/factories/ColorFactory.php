<?php

namespace Database\Factories;

use App\Models\Color;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Color>
 */
class ColorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->safeColorName(),
            'hex_code' => fake()->hexColor(),
        ];
    }
}
