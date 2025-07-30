<?php

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
