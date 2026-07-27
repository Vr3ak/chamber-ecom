<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Product;
use App\Models\Trending;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trending>
 */
class TrendingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'admin_id' => Admin::factory(),
            'product_id' => Product::factory(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    /** Featured but switched off — must behave as not trending. */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
