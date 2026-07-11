<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Chamber catalogue (brands, colours, sizes, products, variants, reviews).
        $this->call(ChamberSeeder::class);

        // Default admin account (admin@chamber.test / password).
        $this->call(AdminSeeder::class);

        // Feature 3 sample order + payment history (Mission 5 scenario).
        $this->call(OrderSeeder::class);
    }
}