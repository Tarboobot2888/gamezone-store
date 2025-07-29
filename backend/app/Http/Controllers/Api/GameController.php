<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $query = Game::query()->where('is_active', true);

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('featured') && $request->featured) {
            $query->where('is_featured', true);
        }

        $games = $query->withCount(['services', 'accounts'])
                      ->orderBy('sort_order')
                      ->orderBy('name')
                      ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $games->items(),
            'pagination' => [
                'current_page' => $games->currentPage(),
                'last_page' => $games->lastPage(),
                'per_page' => $games->perPage(),
                'total' => $games->total(),
            ]
        ]);
    }

    public function show(Game $game)
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game not found'
            ], 404);
        }

        $game->load(['services' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }]);

        return response()->json([
            'success' => true,
            'data' => $game
        ]);
    }

    public function categories()
    {
        $categories = Game::select('category')
                         ->where('is_active', true)
                         ->distinct()
                         ->orderBy('category')
                         ->pluck('category');

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function featured()
    {
        $games = Game::where('is_active', true)
                    ->where('is_featured', true)
                    ->withCount(['services', 'accounts'])
                    ->orderBy('sort_order')
                    ->limit(8)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $games
        ]);
    }
}
