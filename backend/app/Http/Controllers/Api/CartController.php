<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Service;
use App\Models\GameAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::with(['service.game', 'gameAccount.game'])
                        ->where('user_id', Auth::id())
                        ->get();

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->service ? $item->service->price : $item->gameAccount->price);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'total' => $total,
                'count' => $cartItems->count()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'nullable|exists:services,id',
            'game_account_id' => 'nullable|exists:game_accounts,id',
            'quantity' => 'required|integer|min:1|max:10',
            'customer_info' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Ensure either service_id or game_account_id is provided
        if (!$request->service_id && !$request->game_account_id) {
            return response()->json([
                'success' => false,
                'message' => 'Either service_id or game_account_id is required'
            ], 422);
        }

        // Check if item already exists in cart
        $existingItem = Cart::where('user_id', Auth::id())
                           ->where(function ($query) use ($request) {
                               if ($request->service_id) {
                                   $query->where('service_id', $request->service_id);
                               } else {
                                   $query->where('game_account_id', $request->game_account_id);
                               }
                           })
                           ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $request->quantity,
                'customer_info' => $request->customer_info ?? $existingItem->customer_info,
            ]);

            $cartItem = $existingItem;
        } else {
            $cartItem = Cart::create([
                'user_id' => Auth::id(),
                'service_id' => $request->service_id,
                'game_account_id' => $request->game_account_id,
                'quantity' => $request->quantity,
                'customer_info' => $request->customer_info,
            ]);
        }

        $cartItem->load(['service.game', 'gameAccount.game']);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => $cartItem
        ], 201);
    }

    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:10',
            'customer_info' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $cart->update($request->only(['quantity', 'customer_info']));
        $cart->load(['service.game', 'gameAccount.game']);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => $cart
        ]);
    }

    public function destroy(Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully'
        ]);
    }

    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
}
