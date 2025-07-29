<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameAccount;
use App\Models\Game;
use Illuminate\Http\Request;

class GameAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = GameAccount::with('game')->where('status', 'available');

        if ($request->has('game_id') && $request->game_id) {
            $query->where('game_id', $request->game_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('level_min') && $request->level_min) {
            $query->where('level', '>=', $request->level_min);
        }

        if ($request->has('level_max') && $request->level_max) {
            $query->where('level', '<=', $request->level_max);
        }

        $accounts = $query->orderBy('is_featured', 'desc')
                         ->orderBy('price')
                         ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $accounts->items(),
            'pagination' => [
                'current_page' => $accounts->currentPage(),
                'last_page' => $accounts->lastPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
            ]
        ]);
    }

    public function show(GameAccount $account)
    {
        if ($account->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Account not available'
            ], 404);
        }

        $account->load('game');

        return response()->json([
            'success' => true,
            'data' => $account
        ]);
    }

    public function byGame(Game $game)
    {
        $accounts = $game->accounts()
                        ->where('status', 'available')
                        ->orderBy('is_featured', 'desc')
                        ->orderBy('price')
                        ->limit(12)
                        ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }

    public function featured()
    {
        $accounts = GameAccount::with('game')
                              ->where('status', 'available')
                              ->where('is_featured', true)
                              ->orderBy('price')
                              ->limit(8)
                              ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }
}
