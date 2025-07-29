<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Game;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('game')->where('is_active', true);

        if ($request->has('game_id') && $request->game_id) {
            $query->where('game_id', $request->game_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        $services = $query->orderBy('sort_order')
                         ->orderBy('name')
                         ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $services->items(),
            'pagination' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
            ]
        ]);
    }

    public function show(Service $service)
    {
        if (!$service->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $service->load('game');

        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }

    public function byGame(Game $game)
    {
        $services = $game->services()
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->get();

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    public function types()
    {
        $types = Service::select('type')
                       ->where('is_active', true)
                       ->distinct()
                       ->orderBy('type')
                       ->pluck('type');

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }
}
