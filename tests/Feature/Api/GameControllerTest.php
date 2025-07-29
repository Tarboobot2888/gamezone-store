<?php

namespace Tests\Feature\Api;

use App\Models\Game;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_games()
    {
        Game::factory()->count(3)->create(['is_active' => true]);
        Game::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/games');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    public function test_can_search_games()
    {
        $game1 = Game::factory()->create(['name' => 'PUBG Mobile', 'is_active' => true]);
        $game2 = Game::factory()->create(['name' => 'Free Fire', 'is_active' => true]);

        $response = $this->getJson('/api/v1/games?search=PUBG');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.name', 'PUBG Mobile');
    }

    public function test_can_show_game_with_services()
    {
        $game = Game::factory()->create(['is_active' => true]);
        Service::factory()->count(2)->create(['game_id' => $game->id, 'is_active' => true]);

        $response = $this->getJson("/api/v1/games/{$game->id}");

        $response->assertStatus(200)
                ->assertJsonPath('name', $game->name)
                ->assertJsonCount(2, 'services');
    }

    public function test_can_get_game_services()
    {
        $game = Game::factory()->create(['is_active' => true]);
        Service::factory()->count(3)->create(['game_id' => $game->id, 'is_active' => true]);
        Service::factory()->create(['game_id' => $game->id, 'is_active' => false]);

        $response = $this->getJson("/api/v1/games/{$game->id}/services");

        $response->assertStatus(200)
                ->assertJsonCount(3);
    }

    public function test_can_get_popular_games()
    {
        $game1 = Game::factory()->create(['is_active' => true]);
        $game2 = Game::factory()->create(['is_active' => true]);
        
        Service::factory()->count(5)->create(['game_id' => $game1->id]);
        Service::factory()->count(2)->create(['game_id' => $game2->id]);

        $response = $this->getJson('/api/v1/games/popular');

        $response->assertStatus(200);
        $this->assertEquals($game1->id, $response->json()[0]['id']);
    }
}
