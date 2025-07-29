<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            GameSeeder::class,
            ServiceSeeder::class,
            GameAccountSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
