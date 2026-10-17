<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default admin account for Sutravomar Stone Luxe
        User::updateOrCreate(
            ['email' => 'admin@pierre.com'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('admin123456'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@sutravomar.ma'],
            [
                'name' => 'Admin Sutravomar',
                'password' => Hash::make('admin123456'),
                'role' => 'admin',
            ]
        );
    }
}
