<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gamezone.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'preferred_language' => 'en',
            'theme_preference' => 'dark',
        ]);
        $superAdmin->assignRole('super-admin');

        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin2@gamezone.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'preferred_language' => 'en',
            'theme_preference' => 'dark',
        ]);
        $admin->assignRole('admin');

        // Create Support User
        $support = User::create([
            'name' => 'Support User',
            'email' => 'support@gamezone.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'preferred_language' => 'en',
            'theme_preference' => 'light',
        ]);
        $support->assignRole('support');

        // Create Demo Users
        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });
    }
}
