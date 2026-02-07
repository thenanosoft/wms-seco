<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (Main Desk)
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Main Desk Admin',
                'email' => 'farhan987ellahi@gmail.com',
                'password' => Hash::make('admin@123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Store Helper
        User::updateOrCreate(
            ['username' => 'helper'],
            [
                'name' => 'Store Helper',
                'email' => 'helper@local.test',
                'password' => Hash::make('helper@123'),
                'role' => 'store_helper',
                'is_active' => true,
            ]
        );
    }
}
