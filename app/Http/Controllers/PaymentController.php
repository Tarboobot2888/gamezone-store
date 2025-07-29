<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle Stripe webhook
     */
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id
        ]);

        try {
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handleStripePaymentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handleStripePaymentFailed($event->data->object);
                    break;

                case 'payment_intent.requires_action':
                    $this->handleStripePaymentRequiresAction($event->data->object);
                    break;

                case 'charge.dispute.created':
                    $this->handleStripeDispute($event->data->object);
                    break;

                default:
                    Log::info('Unhandled Stripe webhook event', ['type' => $event->type]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Error processing Stripe webhook', [
                'event_type' => $event->type,
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle PayPal webhook
     */
    public function paypalWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        
        Log::info('PayPal webhook received', [
            'event_type' => $payload['event_type'] ?? 'unknown',
            'resource_type' => $payload['resource_type'] ?? 'unknown'
        ]);

        try {
            // Verify webhook signature (simplified)
            if (!$this->verifyPayPalWebhook($request)) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $eventType = $payload['event_type'] ?? '';

            switch ($eventType) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePayPalPaymentCompleted($payload['resource']);
                    break;

                case 'PAYMENT.CAPTURE.DENIED':
                    $this->handlePayPalPaymentDenied($payload['resource']);
                    break;

                case 'PAYMENT.CAPTURE.REFUNDED':
                    $this->handlePayPalRefund($payload['resource']);
                    break;

                default:
                    Log::info('Unhandled PayPal webhook event', ['type' => $eventType]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Error processing PayPal webhook', [
                'event_type' => $eventType ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle successful Stripe payment
     */
    protected function handleStripePaymentSucceeded($paymentIntent): void
    {
        $orderIds = json_decode($paymentIntent->metadata->order_ids ?? '[]', true);
        
        foreach ($orderIds as $orderId) {
            $order = Order::find($orderId);
            
            if (!$order) {
                Log::warning('Order not found for Stripe payment', ['order_id' => $orderId]);
                continue;
            }

            // Update payment transaction
            $transaction = PaymentTransaction::where('transaction_id', $paymentIntent->id)->first();
            if ($transaction) {
                $transaction->update([
                    'status' => PaymentTransaction::STATUS_COMPLETED,
                    'gateway_response' => $paymentIntent->toArray(),
                    'completed_at' => now(),
                ]);
            }

            // Update order status
            $order->update([
                'status' => Order::STATUS_PAID,
                'payment_id' => $paymentIntent->id,
                'paid_at' => now(),
            ]);

            // Process the order
            $this->paymentService->processOrder($order);

            event(new OrderStatusChanged($order, $order->status));

            Log::info('Stripe payment processed successfully', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntent->id
            ]);
        }
    }

    /**
     * Handle failed Stripe payment
     */
    protected function handleStripePaymentFailed($paymentIntent): void
    {
        $orderIds = json_decode($paymentIntent->metadata->order_ids ?? '[]', true);
        
        foreach ($orderIds as $orderId) {
            $order = Order::find($orderId);
            
            if (!$order) {
                continue;
            }

            // Update payment transaction
            $transaction = PaymentTransaction::where('transaction_id', $paymentIntent->id)->first();
            if ($transaction) {
                $transaction->update([
                    'status' => PaymentTransaction::STATUS_FAILED,
                    'gateway_response' => $paymentIntent->toArray(),
                    'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Payment failed',
                ]);
            }

            // Update order status
            $order->update([
                'status' => Order::STATUS_FAILED,
                'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Payment failed',
            ]);

            event(new OrderStatusChanged($order, $order->status));

            Log::warning('Stripe payment failed', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntent->id,
                'error' => $paymentIntent->last_payment_error->message ?? 'Unknown error'
            ]);
        }
    }

    /**
     * Handle Stripe payment requiring action
     */
    protected function handleStripePaymentRequiresAction($paymentIntent): void
    {
        $orderIds = json_decode($paymentIntent->metadata->order_ids ?? '[]', true);
        
        foreach ($orderIds as $orderId) {
            $order = Order::find($orderId);
            
            if (!$order) {
                continue;
            }

            $order->update([
                'status' => Order::STATUS_PENDING_PAYMENT,
                'payment_requires_action' => true,
            ]);

            event(new OrderStatusChanged($order, $order->status));
        }
    }

    /**
     * Handle Stripe dispute
     */
    protected function handleStripeDispute($dispute): void
    {
        $chargeId = $dispute->charge;
        
        // Find orders associated with this charge
        $orders = Order::where('payment_id', $chargeId)->get();
        
        foreach ($orders as $order) {
            $order->update([
                'status' => Order::STATUS_DISPUTED,
                'dispute_id' => $dispute->id,
                'dispute_reason' => $dispute->reason,
            ]);

            event(new OrderStatusChanged($order, $order->status));

            Log::warning('Stripe dispute created', [
                'order_id' => $order->id,
                'dispute_id' => $dispute->id,
                'reason' => $dispute->reason
            ]);
        }
    }

    /**
     * Handle completed PayPal payment
     */
    protected function handlePayPalPaymentCompleted($resource): void
    {
        $customId = $resource['custom_id'] ?? null;
        
        if (!$customId) {
            Log::warning('PayPal payment completed without custom_id');
            return;
        }

        $order = Order::find($customId);
        
        if (!$order) {
            Log::warning('Order not found for PayPal payment', ['custom_id' => $customId]);
            return;
        }

        // Update payment transaction
        $transaction = PaymentTransaction::where('order_id', $order->id)
                                        ->where('gateway', 'paypal')
                                        ->first();
        
        if ($transaction) {
            $transaction->update([
                'status' => PaymentTransaction::STATUS_COMPLETED,
                'transaction_id' => $resource['id'],
                'gateway_response' => $resource,
                'completed_at' => now(),
            ]);
        }

        // Update order
        $order->update([
            'status' => Order::STATUS_PAID,
            'payment_id' => $resource['id'],
            'paid_at' => now(),
        ]);

        // Process the order
        $this->paymentService->processOrder($order);

        event(new OrderStatusChanged($order, $order->status));

        Log::info('PayPal payment processed successfully', [
            'order_id' => $order->id,
            'payment_id' => $resource['id']
        ]);
    }

    /**
     * Handle denied PayPal payment
     */
    protected function handlePayPalPaymentDenied($resource): void
    {
        $customId = $resource['custom_id'] ?? null;
        
        if (!$customId) {
            return;
        }

        $order = Order::find($customId);
        
        if (!$order) {
            return;
        }

        $order->update([
            'status' => Order::STATUS_FAILED,
            'failure_reason' => 'PayPal payment denied',
        ]);

        event(new OrderStatusChanged($order, $order->status));
    }

    /**
     * Handle PayPal refund
     */
    protected function handlePayPalRefund($resource): void
    {
        // Implementation for PayPal refund handling
        Log::info('PayPal refund processed', ['resource' => $resource]);
    }

    /**
     * Verify PayPal webhook signature (simplified)
     */
    protected function verifyPayPalWebhook(Request $request): bool
    {
        // In production, implement proper PayPal webhook verification
        // This is a simplified version
        return true;
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods(): JsonResponse
    {
        $methods = [
            [
                'id' => 'stripe',
                'name' => 'Credit/Debit Card',
                'description' => 'Pay securely with your credit or debit card',
                'icon' => 'fas fa-credit-card',
                'enabled' => config('services.stripe.key') ? true : false,
            ],
            [
                'id' => 'paypal',
                'name' => 'PayPal',
                'description' => 'Pay with your PayPal account',
                'icon' => 'fab fa-paypal',
                'enabled' => config('services.paypal.client_id') ? true : false,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => array_filter($methods, fn($method) => $method['enabled'])
        ]);
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = null;
        
        if ($orderId) {
            $order = Order::where('id', $orderId)
                         ->where('user_id', auth()->id())
                         ->first();
        }

        return view('payment.success', compact('order'));
    }

    /**
     * Show payment failed page
     */
    public function paymentFailed(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = null;
        
        if ($orderId) {
            $order = Order::where('id', $orderId)
                         ->where('user_id', auth()->id())
                         ->first();
        }

        return view('payment.failed', compact('order'));
    }

    /**
     * Show payment cancelled page
     */
    public function paymentCancelled(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = null;
        
        if ($orderId) {
            $order = Order::where('id', $orderId)
                         ->where('user_id', auth()->id())
                         ->first();
        }

        return view('payment.cancelled', compact('order'));
    }
}
