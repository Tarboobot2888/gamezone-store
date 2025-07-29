<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Events\OrderStatusChanged;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'payment_method' => 'required|in:stripe,paypal',
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
                      ->where('status', 'pending')
                      ->get();

        if ($orders->count() !== count($request->order_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Some orders are not valid or not owned by you'
            ], 422);
        }

        $totalAmount = $orders->sum('total_amount');

        try {
            if ($request->payment_method === 'stripe') {
                $paymentIntent = $this->paymentService->createStripePaymentIntent($totalAmount, $orders);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'client_secret' => $paymentIntent->client_secret,
                        'payment_intent_id' => $paymentIntent->id,
                        'total_amount' => $totalAmount
                    ]
                ]);
            } else {
                $paypalOrder = $this->paymentService->createPayPalOrder($totalAmount, $orders);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'order_id' => $paypalOrder['id'],
                        'approval_url' => $paypalOrder['links'][1]['href'] ?? null,
                        'total_amount' => $totalAmount
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Payment intent creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent'
            ], 500);
        }
    }

    public function confirmStripePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
            'order_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                $this->processSuccessfulPayment($request->order_ids, $paymentIntent->id, 'stripe', $paymentIntent->amount / 100);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Payment confirmed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not completed'
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('Stripe payment confirmation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Payment confirmation failed'
            ], 500);
        }
    }

    public function confirmPayPalPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string',
            'order_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $captureResult = $this->paymentService->capturePayPalOrder($request->order_id);
            
            if ($captureResult['status'] === 'COMPLETED') {
                $amount = $captureResult['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
                $this->processSuccessfulPayment($request->order_ids, $request->order_id, 'paypal', $amount);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Payment confirmed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not completed'
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('PayPal payment confirmation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Payment confirmation failed'
            ], 500);
        }
    }

    protected function processSuccessfulPayment($orderIds, $transactionId, $paymentMethod, $amount)
    {
        DB::beginTransaction();

        try {
            $orders = Order::whereIn('id', $orderIds)->get();

            foreach ($orders as $order) {
                // Create payment transaction
                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId,
                    'payment_method' => $paymentMethod,
                    'amount' => $order->total_amount,
                    'currency' => 'USD',
                    'status' => 'completed',
                    'gateway_response' => json_encode(['transaction_id' => $transactionId]),
                ]);

                // Update order status
                $oldStatus = $order->status;
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // If it's a game account, mark it as sold
                if ($order->game_account_id) {
                    $order->gameAccount->update(['status' => 'sold']);
                }

                event(new OrderStatusChanged($order, $oldStatus));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook.secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handleStripePaymentSuccess($event['data']['object']);
                break;
            case 'payment_intent.payment_failed':
                $this->handleStripePaymentFailure($event['data']['object']);
                break;
        }

        return response('Webhook handled', 200);
    }

    public function paypalWebhook(Request $request)
    {
        // PayPal webhook verification and handling
        $payload = $request->all();
        
        Log::info('PayPal webhook received', $payload);

        switch ($payload['event_type']) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->handlePayPalPaymentSuccess($payload);
                break;
            case 'PAYMENT.CAPTURE.DENIED':
                $this->handlePayPalPaymentFailure($payload);
                break;
        }

        return response('Webhook handled', 200);
    }

    protected function handleStripePaymentSuccess($paymentIntent)
    {
        Log::info('Stripe payment succeeded', ['payment_intent_id' => $paymentIntent['id']]);
        // Additional processing if needed
    }

    protected function handleStripePaymentFailure($paymentIntent)
    {
        Log::info('Stripe payment failed', ['payment_intent_id' => $paymentIntent['id']]);
        // Handle payment failure
    }

    protected function handlePayPalPaymentSuccess($payload)
    {
        Log::info('PayPal payment succeeded', ['order_id' => $payload['resource']['id']]);
        // Additional processing if needed
    }

    protected function handlePayPalPaymentFailure($payload)
    {
        Log::info('PayPal payment failed', ['order_id' => $payload['resource']['id']]);
        // Handle payment failure
    }
}
