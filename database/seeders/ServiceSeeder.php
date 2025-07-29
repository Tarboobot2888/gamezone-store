<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $games = Game::all();

        foreach ($games as $game) {
            $this->createServicesForGame($game);
        }
    }

    private function createServicesForGame(Game $game)
    {
        $services = [];

        switch ($game->slug) {
            case 'pubg-mobile':
                $services = [
                    ['name' => '60 UC', 'price' => 0.99, 'package_type' => 'UC', 'package_value' => '60'],
                    ['name' => '325 UC', 'price' => 4.99, 'package_type' => 'UC', 'package_value' => '325'],
                    ['name' => '660 UC', 'price' => 9.99, 'package_type' => 'UC', 'package_value' => '660'],
                    ['name' => '1800 UC', 'price' => 24.99, 'package_type' => 'UC', 'package_value' => '1800'],
                    ['name' => '3850 UC', 'price' => 49.99, 'package_type' => 'UC', 'package_value' => '3850'],
                ];
                break;

            case 'free-fire':
                $services = [
                    ['name' => '100 Diamonds', 'price' => 0.99, 'package_type' => 'Diamonds', 'package_value' => '100'],
                    ['name' => '520 Diamonds', 'price' => 4.99, 'package_type' => 'Diamonds', 'package_value' => '520'],
                    ['name' => '1080 Diamonds', 'price' => 9.99, 'package_type' => 'Diamonds', 'package_value' => '1080'],
                    ['name' => '2200 Diamonds', 'price' => 19.99, 'package_type' => 'Diamonds', 'package_value' => '2200'],
                    ['name' => '5600 Diamonds', 'price' => 49.99, 'package_type' => 'Diamonds', 'package_value' => '5600'],
                ];
                break;

            case 'mobile-legends':
                $services = [
                    ['name' => '86 Diamonds', 'price' => 0.99, 'package_type' => 'Diamonds', 'package_value' => '86'],
                    ['name' => '172 Diamonds', 'price' => 1.99, 'package_type' => 'Diamonds', 'package_value' => '172'],
                    ['name' => '514 Diamonds', 'price' => 4.99, 'package_type' => 'Diamonds', 'package_value' => '514'],
                    ['name' => '1050 Diamonds', 'price' => 9.99, 'package_type' => 'Diamonds', 'package_value' => '1050'],
                    ['name' => '2195 Diamonds', 'price' => 19.99, 'package_type' => 'Diamonds', 'package_value' => '2195'],
                ];
                break;

            case 'genshin-impact':
                $services = [
                    ['name' => '60 Genesis Crystals', 'price' => 0.99, 'package_type' => 'Genesis Crystals', 'package_value' => '60'],
                    ['name' => '300 Genesis Crystals', 'price' => 4.99, 'package_type' => 'Genesis Crystals', 'package_value' => '300'],
                    ['name' => '980 Genesis Crystals', 'price' => 14.99, 'package_type' => 'Genesis Crystals', 'package_value' => '980'],
                    ['name' => '1980 Genesis Crystals', 'price' => 29.99, 'package_type' => 'Genesis Crystals', 'package_value' => '1980'],
                    ['name' => '3280 Genesis Crystals', 'price' => 49.99, 'package_type' => 'Genesis Crystals', 'package_value' => '3280'],
                ];
                break;

            case 'cod-mobile':
                $services = [
                    ['name' => '80 CP', 'price' => 0.99, 'package_type' => 'CP', 'package_value' => '80'],
                    ['name' => '400 CP', 'price' => 4.99, 'package_type' => 'CP', 'package_value' => '400'],
                    ['name' => '800 CP', 'price' => 9.99, 'package_type' => 'CP', 'package_value' => '800'],
                    ['name' => '2000 CP', 'price' => 24.99, 'package_type' => 'CP', 'package_value' => '2000'],
                    ['name' => '5200 CP', 'price' => 59.99, 'package_type' => 'CP', 'package_value' => '5200'],
                ];
                break;

            case 'valorant':
                $services = [
                    ['name' => '475 VP', 'price' => 4.99, 'package_type' => 'VP', 'package_value' => '475'],
                    ['name' => '1000 VP', 'price' => 9.99, 'package_type' => 'VP', 'package_value' => '1000'],
                    ['name' => '2050 VP', 'price' => 19.99, 'package_type' => 'VP', 'package_value' => '2050'],
                    ['name' => '3650 VP', 'price' => 34.99, 'package_type' => 'VP', 'package_value' => '3650'],
                    ['name' => '5350 VP', 'price' => 49.99, 'package_type' => 'VP', 'package_value' => '5350'],
                ];
                break;
        }

        foreach ($services as $index => $serviceData) {
            Service::create([
                'game_id' => $game->id,
                'name' => $serviceData['name'],
                'description' => "Top-up {$serviceData['package_value']} {$serviceData['package_type']} for {$game->name}",
                'price' => $serviceData['price'],
                'currency' => 'USD',
                'package_type' => $serviceData['package_type'],
                'package_value' => $serviceData['package_value'],
                'is_active' => true,
                'is_featured' => $index < 3, // First 3 services are featured
                'sort_order' => $index + 1,
                'processing_time' => '5-10 minutes',
                'requirements' => [
                    'player_id' => 'Player ID/UID',
                    'server' => 'Server/Zone (if applicable)',
                ],
            ]);
        }
    }
}
