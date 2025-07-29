<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameAccount;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GameAccountController extends Controller
{
    /**
     * Display a listing of game accounts
     */
    public function index(Request $request): JsonResponse
    {
        $query = GameAccount::with(['game'])->available();

        // Search functionality
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('level', 'like', '%' . $searchTerm . '%');
            });
        }

        // Game filter
        if ($request->has('game_id')) {
            $query->where('game_id', $request->get('game_id'));
        }

        if ($request->has('game_slug')) {
            $game = Game::where('slug', $request->get('game_slug'))->first();
            if ($game) {
                $query->where('game_id', $game->id);
            }
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        // Platform filter
        if ($request->has('platform')) {
            $query->where('platform', $request->get('platform'));
        }

        // Region filter
        if ($request->has('region')) {
            $query->where('region', $request->get('region'));
        }

        // Level range filter
        if ($request->has('min_level')) {
            $query->where('level', '>=', $request->get('min_level'));
        }

        if ($request->has('max_level')) {
            $query->where('level', '<=', $request->get('max_level'));
        }

        // Sorting
        $sortBy = $request->get('sort', 'newest');
        
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'level_high':
                $query->orderByRaw('CAST(level AS UNSIGNED) DESC');
                break;
            case 'level_low':
                $query->orderByRaw('CAST(level AS UNSIGNED) ASC');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $perPage = min($request->get('per_page', 12), 50);
        $accounts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $accounts,
            'meta' => [
                'total' => $accounts->total(),
                'per_page' => $accounts->perPage(),
                'current_page' => $accounts->currentPage(),
                'last_page' => $accounts->lastPage(),
            ]
        ]);
    }

    /**
     * Display the specified game account
     */
    public function show(GameAccount $account): JsonResponse
    {
        if (!$account->is_available || $account->stock_quantity < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found or unavailable'
            ], 404);
        }

        $account->load(['game']);
        
        // Get related accounts
        $relatedAccounts = GameAccount::where('game_id', $account->game_id)
                                    ->where('id', '!=', $account->id)
                                    ->available()
                                    ->limit(4)
                                    ->get();

        // Increment view count
        $account->increment('view_count');

        return response()->json([
            'success' => true,
            'data' => [
                'account' => $account,
                'related_accounts' => $relatedAccounts
            ]
        ]);
    }

    /**
     * Get accounts by game
     */
    public function byGame(Game $game): JsonResponse
    {
        if (!$game->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Game not found or inactive'
            ], 404);
        }

        $accounts = $game->accounts()
                         ->available()
                         ->orderBy('created_at', 'desc')
                         ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }

    /**
     * Get featured accounts
     */
    public function featured(): JsonResponse
    {
        $accounts = GameAccount::with(['game'])
                              ->available()
                              ->featured()
                              ->orderBy('created_at', 'desc')
                              ->limit(6)
                              ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }

    /**
     * Get account platforms
     */
    public function platforms(): JsonResponse
    {
        $platforms = GameAccount::available()
                               ->select('platform')
                               ->distinct()
                               ->whereNotNull('platform')
                               ->orderBy('platform')
                               ->pluck('platform');

        return response()->json([
            'success' => true,
            'data' => $platforms
        ]);
    }

    /**
     * Get account regions
     */
    public function regions(): JsonResponse
    {
        $regions = GameAccount::available()
                             ->select('region')
                             ->distinct()
                             ->whereNotNull('region')
                             ->orderBy('region')
                             ->pluck('region');

        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }

    /**
     * Get price range for accounts
     */
    public function priceRange(Request $request): JsonResponse
    {
        $query = GameAccount::available();

        if ($request->has('game_id')) {
            $query->where('game_id', $request->get('game_id'));
        }

        $minPrice = $query->min('price') ?? 0;
        $maxPrice = $query->max('price') ?? 1000;

        return response()->json([
            'success' => true,
            'data' => [
                'min_price' => $minPrice,
                'max_price' => $maxPrice
            ]
        ]);
    }
}
