<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a default admin account so you can log in immediately.
 * Change the password before any real deployment.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@chamber.test'],
            [
                'name'     => 'Chamber Admin',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
            ]
        );
    }
}
