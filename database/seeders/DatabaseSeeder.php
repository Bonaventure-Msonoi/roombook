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
        User::updateOrCreate(
            ['email' => 'super@roombook.local'],
            [
                'name' => 'Super User',
                'password' => 'password',
                'role' => User::ROLE_SUPER_USER,
                'department' => 'IT & Digital Transformation',
                'email_verified_at' => now(),
                'is_active' => true,
                'approved_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@roombook.local'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'department' => 'Operations Dept',
                'email_verified_at' => now(),
                'is_active' => true,
                'approved_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@roombook.local'],
            [
                'name' => 'Staff User',
                'password' => 'password',
                'role' => User::ROLE_USER,
                'department' => 'HR',
                'email_verified_at' => now(),
                'is_active' => true,
                'approved_at' => now(),
            ]
        );

        $this->call(RoomSeeder::class);
    }
}
