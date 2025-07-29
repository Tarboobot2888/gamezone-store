<?php

namespace Tests\Unit\Models;

use App\Models\Game;
use App\Models\Service;
use App\Models\GameAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_has_services()
    {
        $game = Game::factory()->create();
        $service = Service::factory()->create(['game_id' => $game->id]);

        $this->assertTrue($game->services->contains($service));
    }

    public function test_game_has_accounts()
    {
        $game = Game::factory()->create();
        $account = GameAccount::factory()->create(['game_id' => $game->id]);

        $this->assertTrue($game->accounts->contains($account));
    }

    public function test_game_scope_active()
    {
        $activeGame = Game::factory()->create(['is_active' => true]);
        $inactiveGame = Game::factory()->create(['is_active' => false]);

        $activeGames = Game::active()->get();

        $this->assertTrue($activeGames->contains($activeGame));
        $this->assertFalse($activeGames->contains($inactiveGame));
    }

    public function test_game_image_url_attribute()
    {
        $game = Game::factory()->create(['image' => 'games/test.jpg']);

        $this->assertEquals(asset('storage/games/test.jpg'), $game->image_url);
    }

    public function test_game_image_url_default()
    {
        $game = Game::factory()->create(['image' => null]);

        $this->assertEquals(asset('images/default-game.svg'), $game->image_url);
    }
}
