<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's users.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@londry.test'],
            ['name' => 'Admin Londry', 'password' => 'password', 'role' => Role::Admin],
        );

        User::updateOrCreate(
            ['email' => 'kasir@londry.test'],
            ['name' => 'Kasir Londry', 'password' => 'password', 'role' => Role::Kasir],
        );
    }
}
