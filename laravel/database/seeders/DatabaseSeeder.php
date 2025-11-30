<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure admin user exists (idempotent)
        User::updateOrCreate(
            ['email' => 'admin@mdht.com'],
            [
                'name' => 'Admin MDHT',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '0123456789',
            ]
        );

        // Ensure regular test user exists (idempotent)
        User::updateOrCreate(
            ['email' => 'user@mdht.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'phone' => '0123456788',
            ]
        );
    }
}
