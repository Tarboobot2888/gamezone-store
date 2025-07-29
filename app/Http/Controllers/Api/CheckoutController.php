<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create order from cart items
     */
    public function createOrderFromCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_info' => 'required|array',
            'customer_info.player_id' => 'required|string|max:255',
            'customer_info.server' => 'nullable|string|max:255',
            'customer_info.email' => 'required|email|max:255',
            'customer_info.phone' => 'nullable|string|max:20',
            'customer_info.notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        // Get cart items
        $cartItems = Cart::with(['service.game', 'gameAccount.game'])
                        ->where(function ($query) use ($userId, $sessionId) {
                            if ($userId) {
                                $query->where('user_id', $userId);
                            } else {
                                $query->where('session_id', $sessionId);
                            }
                        })
                        ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $orders = [];

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->service ?? $cartItem->gameAccount;
                
                // Check availability
                if ($cartItem->product_type === 'account' && $product->stock_quantity < $cartItem->quantity) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$product->title}"
                    ], 400);
                }

                $itemTotal = $product->price * $cartItem->quantity;
                $totalAmount += $itemTotal;

                $order = Order::create([
                    'user_id' => $userId,
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'product_type' => $cartItem->product_type,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $product->price,
                    'total_amount' => $itemTotal,
                    'currency' => $product->currency ?? config('app.default_currency', 'USD'),
                    'customer_info' => $request->customer_info,
                    'status' => Order::STATUS_PENDING,
                    'payment_status' => 'pending',
                ]);

                // Reserve stock for accounts
                if ($cartItem->product_type === 'account') {
                    $product->decrement('stock_quantity', $cartItem->quantity);
                }

                $orders[] = $order;
            }

            // Clear cart
            $cartItems->each->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orders created successfully',
                'data' => [
                    'orders' => $orders,
                    'total_amount' => $totalAmount,
                    'currency' => $orders[0]->currency ?? 'USD'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment for orders
     */
    public function processPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'payment_method' => 'required|in:stripe,paypal',
            'payment_data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $orders = Order::whereIn('id', $request->order_ids)
                      ->where('user_id', Auth::id())
                      ->where('status', Order::STATUS_PENDING)
                      ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid orders found'
            ], 400);
        }

        $totalAmount = $orders->sum('total_amount');
        $currency = $orders->first()->currency;

        DB::beginTransaction();
        try {
            // Create payment transaction record
            $transaction = PaymentTransaction::create([
                'user_id' => Auth::id(),
                'order_id' => $orders->first()->id, // Primary order
                'gateway' => $request->payment_method,
                'amount' => $totalAmount,
                'currency' => $currency,
                'status' => 'pending',
                'gateway_data' => $request->payment_data,
            ]);

            // Process payment through service
            $paymentResult = $this->paymentService->processPayment(
                $request->payment_method,
                $totalAmount,
                $currency,
                $request->payment_data,
                [
                    'order_ids' => $request->order_ids,
                    'transaction_id' => $transaction->id,
                    'customer_info' => $orders->first()->customer_info
                ]
            );

            if ($paymentResult['success']) {
                // Update transaction
                $transaction->update([
                    'status' => 'completed',
                    'gateway_transaction_id' => $paymentResult['transaction_id'],
                    'gateway_response' => $paymentResult['gateway_response'],
                    'completed_at' => now(),
                ]);

                // Update orders
                foreach ($orders as $order) {
                    $order->update([
                        'status' => Order::STATUS_PROCESSING,
                        'payment_status' => 'completed',
                        'payment_id' => $paymentResult['transaction_id'],
                        'paid_at' => now(),
                    ]);

                    event(new OrderStatusChanged($order, Order::STATUS_PROCESSING));
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'payment_id' => $paymentResult['transaction_id'],
                        'orders' => $orders->pluck('order_number')
                    ]
                ]);
            } else {
                // Update transaction as failed
                $transaction->update([
                    'status' => 'failed',
                    'failure_reason' => $paymentResult['message'],
                    'gateway_response' => $paymentResult['gateway_response'] ?? null,
                ]);

                // Restore stock for failed payments
                foreach ($orders as $order) {
                    if ($order->product_type === 'account') {
                        $order->gameAccount->increment('stock_quantity', $order->quantity);
                    }
                    
                    $order->update([
                        'status' => Order::STATUS_CANCELLED,
                        'payment_status' => 'failed',
                    ]);
                }

                DB::rollback();

                return response()->json([
                    'success' => false,
                    'message' => $paymentResult['message']
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();
            
            // Restore stock on exception
            foreach ($orders as $order) {
                if ($order->product_type === 'account') {
                    $order->gameAccount->increment('stock_quantity', $order->quantity);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update customer information for an order
     */
    public function updateCustomerInfo(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validator = Validator::make($request->all(), [
            'customer_info' => 'required|array',
            'customer_info.player_id' => 'required|string|max:255',
            'customer_info.server' => 'nullable|string|max:255',
            'customer_info.email' => 'required|email|max:255',
            'customer_info.phone' => 'nullable|string|max:20',
            'customer_info.notes' => 'nullable|string|max:1000',
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
            'message' => 'Customer information updated successfully',
            'data' => $order
        ]);
    }

    /**
     * Get checkout summary
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        $cartItems = Cart::with(['service.game', 'gameAccount.game'])
                        ->where(function ($query) use ($userId, $sessionId) {
                            if ($userId) {
                                $query->where('user_id', $userId);
                            } else {
                                $query->where('session_id', $sessionId);
                            }
                        })
                        ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        $subtotal = $cartItems->sum(function ($item) {
            $price = $item->service ? $item->service->price : $item->gameAccount->price;
            return $item->quantity * $price;
        });

        $tax = $subtotal * 0.1; // 10% tax (configurable)
        $total = $subtotal + $tax;

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'currency' => config('app.default_currency', 'USD'),
                'item_count' => $cartItems->sum('quantity')
            ]
        ]);
    }
}
