<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Create admin user
        User::factory()->create([
            'name' => 'Admin MDHT',
            'email' => 'admin@mdht.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'phone' => '0123456789',
        ]);

        // Create regular test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@mdht.com',
            'password' => bcrypt('user123'),
            'role' => 'user',
            'phone' => '0123456788',
        ]);
    }
}
