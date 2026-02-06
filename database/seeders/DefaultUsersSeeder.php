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
                'name' => 'Admin Desk',
                'email' => 'admin@wms.test',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Store Helper
        User::updateOrCreate(
            ['username' => 'helper'],
            [
                'name' => 'Helper Desk',
                'email' => 'helper@wms.test',
                'password' => Hash::make('Helper@123'),
                'role' => 'store_helper',
                'is_active' => true,
            ]
        );
    }
}
