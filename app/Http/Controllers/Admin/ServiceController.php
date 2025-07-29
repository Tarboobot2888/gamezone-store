<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Game;
use App\Http\Requests\Admin\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:super-admin|admin']);
    }

    public function index(Request $request)
    {
        $query = Service::with('game');

        if ($request->has('search') && $request->search !== '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('game_id') && $request->game_id !== '') {
            $query->where('game_id', $request->game_id);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->sort_by;
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $services = $query->paginate(20);
        $games = Game::active()->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $services,
                'games' => $games
            ]);
        }

        return view('admin.services.index', compact('services', 'games'));
    }

    public function show(Service $service)
    {
        $service->load(['game', 'orders' => function($query) {
            $query->latest()->limit(10);
        }]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $service
            ]);
        }

        return view('admin.services.show', compact('service'));
    }

    public function create()
    {
        $games = Game::active()->get();
        return view('admin.services.create', compact('games'));
    }

    public function store(ServiceRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service = Service::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Service created successfully'),
                'data' => $service->load('game')
            ], 201);
        }

        return redirect()->route('admin.services.index')
            ->with('success', __('Service created successfully'));
    }

    public function edit(Service $service)
    {
        $games = Game::active()->get();
        return view('admin.services.edit', compact('service', 'games'));
    }

    public function update(ServiceRequest $request, Service $service)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Service updated successfully'),
                'data' => $service->load('game')
            ]);
        }

        return redirect()->route('admin.services.index')
            ->with('success', __('Service updated successfully'));
    }

    public function destroy(Service $service)
    {
        // Check if service has orders
        if ($service->orders()->count() > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cannot delete service with existing orders')
                ], 422);
            }

            return redirect()->route('admin.services.index')
                ->with('error', __('Cannot delete service with existing orders'));
        }

        // Delete image
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Service deleted successfully')
            ]);
        }

        return redirect()->route('admin.services.index')
            ->with('success', __('Service deleted successfully'));
    }

    public function toggleStatus(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);

        $message = $service->is_active 
            ? __('Service activated successfully')
            : __('Service deactivated successfully');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $service->is_active
            ]);
        }

        return redirect()->route('admin.services.index')->with('success', $message);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'services' => 'required|array',
            'services.*' => 'exists:services,id'
        ]);

        $services = Service::whereIn('id', $request->services);

        switch ($request->action) {
            case 'activate':
                $services->update(['is_active' => true]);
                $message = __('Services activated successfully');
                break;
            case 'deactivate':
                $services->update(['is_active' => false]);
                $message = __('Services deactivated successfully');
                break;
            case 'delete':
                // Check if any service has orders
                $servicesWithOrders = $services->whereHas('orders')->count();
                if ($servicesWithOrders > 0) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('Cannot delete services with existing orders')
                        ], 422);
                    }
                    return redirect()->route('admin.services.index')
                        ->with('error', __('Cannot delete services with existing orders'));
                }
                
                $services->delete();
                $message = __('Services deleted successfully');
                break;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->route('admin.services.index')->with('success', $message);
    }

    public function duplicate(Service $service)
    {
        $newService = $service->replicate();
        $newService->name = $service->name . ' (Copy)';
        $newService->is_active = false;
        $newService->save();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Service duplicated successfully'),
                'data' => $newService->load('game')
            ]);
        }

        return redirect()->route('admin.services.edit', $newService)
            ->with('success', __('Service duplicated successfully'));
    }
}
