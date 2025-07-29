<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * Display a listing of services
     */
    public function index(Request $request): JsonResponse
    {
        $query = Service::with(['game'])->active();

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

        // Search functionality
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->get('category'));
        }

        // Sorting
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->withCount('orders')->orderByDesc('orders_count');
                break;
            default:
                $query->orderBy('sort_order', 'asc');
        }

        $perPage = min($request->get('per_page', 12), 50);
        $services = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $services,
            'meta' => [
                'total' => $services->total(),
                'per_page' => $services->perPage(),
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
            ]
        ]);
    }

    /**
     * Display the specified service
     */
    public function show(Service $service): JsonResponse
    {
        if (!$service->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found or inactive'
            ], 404);
        }

        $service->load(['game']);

        // Get related services
        $relatedServices = Service::where('game_id', $service->game_id)
                                 ->where('id', '!=', $service->id)
                                 ->active()
                                 ->limit(4)
                                 ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'service' => $service,
                'related_services' => $relatedServices
            ]
        ]);
    }

    /**
     * Get services by game
     */
    public function byGame(Game $game): JsonResponse
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
     * Get featured services
     */
    public function featured(): JsonResponse
    {
        $services = Service::with(['game'])
                          ->active()
                          ->featured()
                          ->orderBy('sort_order')
                          ->limit(8)
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Get service categories
     */
    public function categories(): JsonResponse
    {
        $categories = Service::active()
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
     * Get price range for services
     */
    public function priceRange(Request $request): JsonResponse
    {
        $query = Service::active();

        if ($request->has('game_id')) {
            $query->where('game_id', $request->get('game_id'));
        }

        $minPrice = $query->min('price') ?? 0;
        $maxPrice = $query->max('price') ?? 100;

        return response()->json([
            'success' => true,
            'data' => [
                'min_price' => $minPrice,
                'max_price' => $maxPrice
            ]
        ]);
    }
}
