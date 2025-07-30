<?php
use App\Models\Game;

test('game has name attribute', function () {
    $game = new Game(['name' => 'Test']);
    expect($game->name)->toBe('Test');
});
