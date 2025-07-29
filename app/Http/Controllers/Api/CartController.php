<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Service;
use App\Models\GameAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display cart contents
     */
    public function index(Request $request): JsonResponse
    {
        $sessionId = $request->session()->getId();
        $userId = Auth::id();

        $query = Cart::with(['service.game', 'gameAccount.game']);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $cartItems = $query->orderBy('created_at', 'desc')->get();

        $total = $cartItems->sum(function ($item) {
            $price = $item->service ? $item->service->price : $item->gameAccount->price;
            return $item->quantity * $price;
        });

        $count = $cartItems->sum('quantity');

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'total' => $total,
                'count' => $count,
                'currency' => config('app.default_currency', 'USD')
            ]
        ]);
    }

    /**
     * Add item to cart
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_type' => 'required|in:service,account',
            'product_id' => 'required|integer',
            'quantity' => 'integer|min:1|max:10'
        ]);

        $sessionId = $request->session()->getId();
        $userId = Auth::id();
        $quantity = $request->get('quantity', 1);

        // Validate product exists and is available
        if ($request->product_type === 'service') {
            $product = Service::find($request->product_id);
            if (!$product || !$product->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service is not available'
                ], 400);
            }
        } else {
            $product = GameAccount::find($request->product_id);
            if (!$product || !$product->is_available || $product->stock_quantity < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not available'
                ], 400);
            }

            // For accounts, limit quantity to available stock
            if ($quantity > $product->stock_quantity) {
                $quantity = $product->stock_quantity;
            }
        }

        DB::beginTransaction();
        try {
            // Check if item already in cart
            $existingItem = Cart::where(function ($query) use ($userId, $sessionId) {
                                    if ($userId) {
                                        $query->where('user_id', $userId);
                                    } else {
                                        $query->where('session_id', $sessionId);
                                    }
                                })
                                ->where('product_type', $request->product_type)
                                ->where('product_id', $request->product_id)
                                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $quantity;
                
                // Check stock for accounts
                if ($request->product_type === 'account' && $newQuantity > $product->stock_quantity) {
                    $newQuantity = $product->stock_quantity;
                }
                
                $existingItem->update(['quantity' => $newQuantity]);
                $cartItem = $existingItem;
            } else {
                $cartItem = Cart::create([
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'product_type' => $request->product_type,
                    'product_id' => $request->product_id,
                    'quantity' => $quantity,
                ]);
            }

            // Get updated cart count
            $cartCount = Cart::where(function ($query) use ($userId, $sessionId) {
                                if ($userId) {
                                    $query->where('user_id', $userId);
                                } else {
                                    $query->where('session_id', $sessionId);
                                }
                            })->sum('quantity');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => [
                    'cart_item' => $cartItem->load(['service.game', 'gameAccount.game']),
                    'cart_count' => $cartCount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart'
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, Cart $cart): JsonResponse
    {
        $this->authorize('update', $cart);

        $request->validate([
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        $quantity = $request->get('quantity');

        // Check stock for accounts
        if ($cart->product_type === 'account') {
            $account = $cart->gameAccount;
            if ($quantity > $account->stock_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested quantity exceeds available stock'
                ], 400);
            }
        }

        $cart->update(['quantity' => $quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'data' => $cart->load(['service.game', 'gameAccount.game'])
        ]);
    }

    /**
     * Remove item from cart
     */
    public function destroy(Cart $cart): JsonResponse
    {
        $this->authorize('delete', $cart);

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully'
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request): JsonResponse
    {
        $sessionId = $request->session()->getId();
        $userId = Auth::id();

        $query = Cart::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $deletedCount = $query->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'data' => ['deleted_count' => $deletedCount]
        ]);
    }

    /**
     * Get cart item count
     */
    public function count(Request $request): JsonResponse
    {
        $sessionId = $request->session()->getId();
        $userId = Auth::id();

        $count = Cart::where(function ($query) use ($userId, $sessionId) {
                        if ($userId) {
                            $query->where('user_id', $userId);
                        } else {
                            $query->where('session_id', $sessionId);
                        }
                    })->sum('quantity');

        return response()->json([
            'success' => true,
            'data' => ['count' => $count]
        ]);
    }

    /**
     * Sync guest cart with user cart after login
     */
    public function sync(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $sessionId = $request->session()->getId();
        $userId = Auth::id();

        DB::beginTransaction();
        try {
            // Get guest cart items
            $guestCartItems = Cart::where('session_id', $sessionId)
                                 ->whereNull('user_id')
                                 ->get();

            foreach ($guestCartItems as $guestItem) {
                // Check if user already has this item in cart
                $existingItem = Cart::where('user_id', $userId)
                                  ->where('product_type', $guestItem->product_type)
                                  ->where('product_id', $guestItem->product_id)
                                  ->first();

                if ($existingItem) {
                    // Merge quantities
                    $existingItem->increment('quantity', $guestItem->quantity);
                    $guestItem->delete();
                } else {
                    // Transfer to user
                    $guestItem->update(['user_id' => $userId]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cart synced successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync cart'
            ], 500);
        }
    }
}
