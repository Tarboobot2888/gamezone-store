<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameAccount;
use Illuminate\Database\Seeder;

class GameAccountSeeder extends Seeder
{
    public function run(): void
    {
        $games = Game::all();

        foreach ($games as $game) {
            $this->createAccountsForGame($game);
        }
    }

    private function createAccountsForGame(Game $game)
    {
        $accounts = [];

        switch ($game->slug) {
            case 'pubg-mobile':
                $accounts = [
                    [
                        'title' => 'PUBG Mobile Conqueror Account',
                        'description' => 'High-tier PUBG Mobile account with Conqueror rank and rare skins.',
                        'price' => 299.99,
                        'platform' => 'Android/iOS',
                        'region' => 'Global',
                        'level' => '85',
                        'rank' => 'Conqueror',
                        'specifications' => [
                            'Season Rank' => 'Conqueror',
                            'Level' => '85',
                            'UC Balance' => '2500',
                            'Rare Skins' => '15+',
                            'Mythic Items' => '3',
                            'Account Age' => '2 years',
                        ],
                    ],
                    [
                        'title' => 'PUBG Mobile Crown Account',
                        'description' => 'Well-maintained PUBG Mobile account with Crown rank.',
                        'price' => 149.99,
                        'platform' => 'Android/iOS',
                        'region' => 'Global',
                        'level' => '65',
                        'rank' => 'Crown',
                        'specifications' => [
                            'Season Rank' => 'Crown',
                            'Level' => '65',
                            'UC Balance' => '1200',
                            'Rare Skins' => '8+',
                            'Mythic Items' => '1',
                            'Account Age' => '1.5 years',
                        ],
                    ],
                ];
                break;

            case 'free-fire':
                $accounts = [
                    [
                        'title' => 'Free Fire Heroic Account',
                        'description' => 'Premium Free Fire account with Heroic rank and exclusive items.',
                        'price' => 199.99,
                        'platform' => 'Android/iOS',
                        'region' => 'Global',
                        'level' => '70',
                        'rank' => 'Heroic',
                        'specifications' => [
                            'Rank' => 'Heroic',
                            'Level' => '70',
                            'Diamonds' => '5000+',
                            'Characters' => 'All Premium',
                            'Pets' => 'All Unlocked',
                            'Skins' => '20+ Rare',
                        ],
                    ],
                ];
                break;

            case 'mobile-legends':
                $accounts = [
                    [
                        'title' => 'Mobile Legends Mythic Account',
                        'description' => 'High-rank Mobile Legends account with all heroes unlocked.',
                        'price' => 249.99,
                        'platform' => 'Android/iOS',
                        'region' => 'Global',
                        'level' => '60',
                        'rank' => 'Mythic',
                        'specifications' => [
                            'Rank' => 'Mythic',
                            'Level' => '60',
                            'Heroes' => 'All Unlocked',
                            'Skins' => '30+ Epic/Legend',
                            'Emblems' => 'Max Level',
                            'Diamonds' => '3000+',
                        ],
                    ],
                ];
                break;
        }

        foreach ($accounts as $index => $accountData) {
            GameAccount::create([
                'game_id' => $game->id,
                'title' => $accountData['title'],
                'description' => $accountData['description'],
                'price' => $accountData['price'],
                'currency' => 'USD',
                'platform' => $accountData['platform'],
                'region' => $accountData['region'],
                'level' => $accountData['level'],
                'rank' => $accountData['rank'],
                'specifications' => $accountData['specifications'],
                'is_available' => true,
                'is_featured' => true,
                'stock_quantity' => 1,
                'sold_count' => 0,
                'login_credentials' => [
                    'email' => 'account' . ($index + 1) . '@example.com',
                    'password' => 'SecurePassword123',
                    'additional_info' => 'Account will be transferred after purchase verification.',
                ],
            ]);
        }
    }
}
