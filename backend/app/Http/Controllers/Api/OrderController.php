<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Service;
use App\Models\GameAccount;
use App\Events\OrderCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['service.game', 'gameAccount.game'])
                     ->where('user_id', Auth::id());

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->orderBy('created_at', 'desc')
                       ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $order->load(['service.game', 'gameAccount.game', 'paymentTransaction']);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'nullable|exists:services,id',
            'game_account_id' => 'nullable|exists:game_accounts,id',
            'quantity' => 'required|integer|min:1|max:10',
            'customer_info' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$request->service_id && !$request->game_account_id) {
            return response()->json([
                'success' => false,
                'message' => 'Either service_id or game_account_id is required'
            ], 422);
        }

        DB::beginTransaction();

        try {
            if ($request->service_id) {
                $service = Service::findOrFail($request->service_id);
                $totalAmount = $service->price * $request->quantity;
                $item = $service;
            } else {
                $gameAccount = GameAccount::findOrFail($request->game_account_id);
                if ($gameAccount->status !== 'available') {
                    throw new \Exception('Game account is not available');
                }
                $totalAmount = $gameAccount->price;
                $item = $gameAccount;
            }

            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'service_id' => $request->service_id,
                'game_account_id' => $request->game_account_id,
                'quantity' => $request->quantity,
                'unit_price' => $item->price,
                'total_amount' => $totalAmount,
                'customer_info' => $request->customer_info,
                'status' => 'pending',
            ]);

            // If it's a game account, mark it as reserved
            if ($request->game_account_id) {
                $gameAccount->update(['status' => 'reserved']);
            }

            DB::commit();

            $order->load(['service.game', 'gameAccount.game']);

            event(new OrderCreated($order));

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createFromCart(Request $request)
    {
        $cartItems = Cart::with(['service', 'gameAccount'])
                        ->where('user_id', Auth::id())
                        ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $orders = [];
            $totalAmount = 0;

            foreach ($cartItems as $cartItem) {
                if ($cartItem->service) {
                    $item = $cartItem->service;
                    $itemTotal = $item->price * $cartItem->quantity;
                } else {
                    $item = $cartItem->gameAccount;
                    if ($item->status !== 'available') {
                        throw new \Exception("Game account {$item->title} is not available");
                    }
                    $itemTotal = $item->price;
                }

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'service_id' => $cartItem->service_id,
                    'game_account_id' => $cartItem->game_account_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $item->price,
                    'total_amount' => $itemTotal,
                    'customer_info' => $cartItem->customer_info,
                    'status' => 'pending',
                ]);

                // If it's a game account, mark it as reserved
                if ($cartItem->game_account_id) {
                    $item->update(['status' => 'reserved']);
                }

                $orders[] = $order;
                $totalAmount += $itemTotal;

                event(new OrderCreated($order));
            }

            // Clear the cart
            Cart::where('user_id', Auth::id())->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orders created successfully from cart',
                'data' => [
                    'orders' => $orders,
                    'total_amount' => $totalAmount,
                    'count' => count($orders)
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create orders: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $order->update(['status' => 'cancelled']);

            // If it's a game account, make it available again
            if ($order->game_account_id) {
                $order->gameAccount->update(['status' => 'available']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateCustomerInfo(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order customer info cannot be updated'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'customer_info' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update(['customer_info' => $request->customer_info]);

        return response()->json([
            'success' => true,
            'message' => 'Customer info updated successfully',
            'data' => $order
        ]);
    }
}
