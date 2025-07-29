<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    /**
     * Display a listing of games
     */
    public function index(Request $request): JsonResponse
    {
        $query = Game::with(['services' => function ($query) {
            $query->active()->orderBy('sort_order');
        }])->active();

        // Search functionality
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->get('category'));
        }

        // Platform filter
        if ($request->has('platform')) {
            $query->where('platform', $request->get('platform'));
        }

        // Sorting
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'popularity':
                $query->withCount('services')->orderBy('services_count', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('sort_order', 'asc');
        }

        $perPage = min($request->get('per_page', 12), 50);
        $games = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $games,
            'meta' => [
                'total' => $games->total(),
                'per_page' => $games->perPage(),
                'current_page' => $games->currentPage(),
                'last_page' => $games->lastPage(),
            ]
        ]);
    }

    /**
     * Display the specified game
     */
    public function show(Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game not found or inactive'
            ], 404);
        }

        $game->load([
            'services' => function ($query) {
                $query->active()->orderBy('sort_order');
            },
            'accounts' => function ($query) {
                $query->available()->orderBy('created_at', 'desc')->limit(6);
            }
        ]);

        // Increment view count
        $game->increment('view_count');

        return response()->json([
            'success' => true,
            'data' => $game
        ]);
    }

    /**
     * Get services for a specific game
     */
    public function services(Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game not found or inactive'
            ], 404);
        }

        $services = $game->services()
                         ->active()
                         ->orderBy('sort_order')
                         ->get();

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Get popular games
     */
    public function popular(): JsonResponse
    {
        $games = Game::active()
                    ->withCount(['services', 'orders'])
                    ->orderByDesc('orders_count')
                    ->orderByDesc('services_count')
                    ->limit(12)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $games
        ]);
    }

    /**
     * Search games
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $games = Game::active()
                    ->where('name', 'like', '%' . $query . '%')
                    ->limit(10)
                    ->get(['id', 'name', 'slug', 'image', 'category']);

        return response()->json([
            'success' => true,
            'data' => $games
        ]);
    }

    /**
     * Get game categories
     */
    public function categories(): JsonResponse
    {
        $categories = Game::active()
                         ->select('category')
                         ->distinct()
                         ->whereNotNull('category')
                         ->orderBy('category')
                         ->pluck('category');

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get featured games
     */
    public function featured(): JsonResponse
    {
        $games = Game::active()
                    ->featured()
                    ->with(['services' => function ($query) {
                        $query->active()->orderBy('sort_order')->limit(3);
                    }])
                    ->orderBy('sort_order')
                    ->limit(8)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $games
        ]);
    }
}
