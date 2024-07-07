<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'test@example.com',
            'password' => bcrypt('12345678'), // Hash the password using bcrypt
            'usertype' => 'admin',
        ]);
    }
}
