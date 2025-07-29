<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameAccount;
use App\Models\Game;
use App\Http\Requests\Admin\GameAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GameAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:super-admin|admin']);
    }

    public function index(Request $request)
    {
        $query = GameAccount::with('game');

        if ($request->has('search') && $request->search !== '') {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('game_id') && $request->game_id !== '') {
            $query->where('game_id', $request->game_id);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('price_range')) {
            $priceRange = explode('-', $request->price_range);
            if (count($priceRange) === 2) {
                $query->whereBetween('price', [$priceRange[0], $priceRange[1]]);
            }
        }

        $accounts = $query->orderBy('created_at', 'desc')->paginate(20);
        $games = Game::active()->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $accounts,
                'games' => $games
            ]);
        }

        return view('admin.accounts.index', compact('accounts', 'games'));
    }

    public function show(GameAccount $account)
    {
        $account->load(['game', 'order']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $account
            ]);
        }

        return view('admin.accounts.show', compact('account'));
    }

    public function create()
    {
        $games = Game::active()->get();
        return view('admin.accounts.create', compact('games'));
    }

    public function store(GameAccountRequest $request)
    {
        $data = $request->validated();

        // Handle image uploads
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('accounts', 'public');
            }
            $data['images'] = $images;
        }

        $account = GameAccount::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Game account created successfully'),
                'data' => $account->load('game')
            ], 201);
        }

        return redirect()->route('admin.accounts.index')
            ->with('success', __('Game account created successfully'));
    }

    public function edit(GameAccount $account)
    {
        $games = Game::active()->get();
        return view('admin.accounts.edit', compact('account', 'games'));
    }

    public function update(GameAccountRequest $request, GameAccount $account)
    {
        $data = $request->validated();

        // Handle image uploads
        if ($request->hasFile('images')) {
            // Delete old images
            if ($account->images) {
                foreach ($account->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('accounts', 'public');
            }
            $data['images'] = $images;
        }

        $account->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Game account updated successfully'),
                'data' => $account->load('game')
            ]);
        }

        return redirect()->route('admin.accounts.index')
            ->with('success', __('Game account updated successfully'));
    }

    public function destroy(GameAccount $account)
    {
        // Check if account is sold
        if ($account->status === 'sold') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cannot delete sold account')
                ], 422);
            }

            return redirect()->route('admin.accounts.index')
                ->with('error', __('Cannot delete sold account'));
        }

        // Delete images
        if ($account->images) {
            foreach ($account->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $account->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Game account deleted successfully')
            ]);
        }

        return redirect()->route('admin.accounts.index')
            ->with('success', __('Game account deleted successfully'));
    }

    public function toggleStatus(GameAccount $account)
    {
        $newStatus = $account->status === 'available' ? 'unavailable' : 'available';
        $account->update(['status' => $newStatus]);

        $message = $newStatus === 'available' 
            ? __('Account marked as available')
            : __('Account marked as unavailable');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $newStatus
            ]);
        }

        return redirect()->route('admin.accounts.index')->with('success', $message);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:available,unavailable,delete',
            'accounts' => 'required|array',
            'accounts.*' => 'exists:game_accounts,id'
        ]);

        $accounts = GameAccount::whereIn('id', $request->accounts);

        switch ($request->action) {
            case 'available':
                $accounts->update(['status' => 'available']);
                $message = __('Accounts marked as available');
                break;
            case 'unavailable':
                $accounts->update(['status' => 'unavailable']);
                $message = __('Accounts marked as unavailable');
                break;
            case 'delete':
                // Check if any account is sold
                $soldAccounts = $accounts->where('status', 'sold')->count();
                if ($soldAccounts > 0) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('Cannot delete sold accounts')
                        ], 422);
                    }
                    return redirect()->route('admin.accounts.index')
                        ->with('error', __('Cannot delete sold accounts'));
                }
                
                $accounts->delete();
                $message = __('Accounts deleted successfully');
                break;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->route('admin.accounts.index')->with('success', $message);
    }
}
