<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a default admin account so you can log in immediately.
 * Change the password before any real deployment.
 *
 * The admin exists in two places:
 *  - admins table  → the API token login (POST /api/admin/login)
 *  - users table   → the shared browser /login page (is_admin = true)
 * Both use admin@chamber.test / password.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@chamber.test'],
            [
                'name' => 'Chamber Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
            ]
        );

        // Same admin as a real user so the single login page recognises them.
        $user = User::firstOrCreate(
            ['email' => 'admin@chamber.test'],
            ['name' => 'Chamber Admin', 'password' => Hash::make('password')]
        );
        $user->forceFill(['is_admin' => true, 'email_verified_at' => now()])->save();
    }
}
