<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use App\Models\GameAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_type' => 'required|in:service,account',
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'customer_details' => 'required|array',
        ]);

        $product = null;
        $unitPrice = 0;

        if ($request->product_type === 'service') {
            $product = Service::findOrFail($request->product_id);
            $unitPrice = $product->price;
        } else {
            $product = GameAccount::findOrFail($request->product_id);
            $unitPrice = $product->price;
            
            if (!$product->is_available || $product->stock_quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is not available or insufficient stock'
                ], 400);
            }
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'order_number' => 'GZ-' . strtoupper(Str::random(8)),
            'product_type' => $request->product_type,
            'product_id' => $request->product_id,
            'service_id' => $request->product_type === 'service' ? $request->product_id : null,
            'game_account_id' => $request->product_type === 'account' ? $request->product_id : null,
            'quantity' => $request->quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $unitPrice * $request->quantity,
            'currency' => $product->currency,
            'customer_details' => $request->customer_details,
        ]);

        return response()->json([
            'success' => true,
            'order' => $order->load(['service.game', 'gameAccount.game']),
            'message' => 'Order created successfully'
        ]);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->canManageOrders()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'order' => $order->load(['user', 'service.game', 'gameAccount.game'])
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        if (!Auth::user()->canManageOrders()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled,refunded',
            'notes' => 'nullable|string',
        ]);

        $order->update([
            'status' => $request->status,
            'notes' => $request->notes,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        // TODO: Send notification to user

        return response()->json([
            'success' => true,
            'order' => $order,
            'message' => 'Order status updated successfully'
        ]);
    }
}
