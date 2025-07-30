<?php
 yg374e-codex/refactor-and-productionize-gamezonestore

namespace Tests\Unit;

use App\Models\Game;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    public function test_game_has_name_attribute(): void
    {
        $game = new Game(['name' => 'Test']);
        $this->assertEquals('Test', $game->name);
    }
}

use App\Models\Game;

test('game has name attribute', function () {
    $game = new Game(['name' => 'Test']);
    expect($game->name)->toBe('Test');
});
 main
