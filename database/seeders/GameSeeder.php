<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            [
                'name' => 'PUBG Mobile',
                'slug' => 'pubg-mobile',
                'description' => 'PlayerUnknown\'s Battlegrounds Mobile - Battle Royale Game',
                'category' => 'Battle Royale',
                'platform' => 'Mobile',
                'is_active' => true,
                'sort_order' => 1,
                'meta_title' => 'PUBG Mobile Top-Up - GameZone',
                'meta_description' => 'Top-up PUBG Mobile UC instantly. Fast and secure service.',
            ],
            [
                'name' => 'Free Fire',
                'slug' => 'free-fire',
                'description' => 'Garena Free Fire - Survival Battle Royale',
                'category' => 'Battle Royale',
                'platform' => 'Mobile',
                'is_active' => true,
                'sort_order' => 2,
                'meta_title' => 'Free Fire Top-Up - GameZone',
                'meta_description' => 'Top-up Free Fire Diamonds instantly. Best prices guaranteed.',
            ],
            [
                'name' => 'Mobile Legends',
                'slug' => 'mobile-legends',
                'description' => 'Mobile Legends: Bang Bang - MOBA Game',
                'category' => 'MOBA',
                'platform' => 'Mobile',
                'is_active' => true,
                'sort_order' => 3,
                'meta_title' => 'Mobile Legends Top-Up - GameZone',
                'meta_description' => 'Top-up Mobile Legends Diamonds. Fast delivery guaranteed.',
            ],
            [
                'name' => 'Genshin Impact',
                'slug' => 'genshin-impact',
                'description' => 'Genshin Impact - Open World Action RPG',
                'category' => 'RPG',
                'platform' => 'Multi-platform',
                'is_active' => true,
                'sort_order' => 4,
                'meta_title' => 'Genshin Impact Top-Up - GameZone',
                'meta_description' => 'Top-up Genshin Impact Genesis Crystals. Secure and instant.',
            ],
            [
                'name' => 'Call of Duty Mobile',
                'slug' => 'cod-mobile',
                'description' => 'Call of Duty: Mobile - FPS Game',
                'category' => 'FPS',
                'platform' => 'Mobile',
                'is_active' => true,
                'sort_order' => 5,
                'meta_title' => 'COD Mobile Top-Up - GameZone',
                'meta_description' => 'Top-up Call of Duty Mobile CP. Fast and reliable service.',
            ],
            [
                'name' => 'Valorant',
                'slug' => 'valorant',
                'description' => 'Valorant - Tactical FPS by Riot Games',
                'category' => 'FPS',
                'platform' => 'PC',
                'is_active' => true,
                'sort_order' => 6,
                'meta_title' => 'Valorant Top-Up - GameZone',
                'meta_description' => 'Top-up Valorant Points instantly. Best prices available.',
            ],
        ];

        foreach ($games as $gameData) {
            Game::create($gameData);
        }
    }
}
