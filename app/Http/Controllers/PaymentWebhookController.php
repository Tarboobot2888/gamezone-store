<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class PaymentWebhookController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload in Stripe webhook', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid signature in Stripe webhook', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event['data']['object']);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event['data']['object']);
                break;
            case 'charge.dispute.created':
                $this->handleChargeDispute($event['data']['object']);
                break;
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event['data']['object']);
                break;
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event['data']['object']);
                break;
            default:
                Log::info('Received unknown Stripe event type', ['type' => $event['type']]);
        }

        return response('Webhook handled', 200);
    }

    public function paypalWebhook(Request $request)
    {
        $payload = $request->all();
        
        // Verify PayPal webhook signature
        if (!$this->verifyPayPalWebhook($request)) {
            Log::error('Invalid PayPal webhook signature');
            return response('Invalid signature', 400);
        }

        $eventType = $payload['event_type'] ?? null;

        switch ($eventType) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->handlePayPalPaymentCompleted($payload);
                break;
            case 'PAYMENT.CAPTURE.DENIED':
                $this->handlePayPalPaymentDenied($payload);
                break;
            case 'PAYMENT.CAPTURE.REFUNDED':
                $this->handlePayPalPaymentRefunded($payload);
                break;
            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                $this->handlePayPalSubscriptionActivated($payload);
                break;
            default:
                Log::info('Received unknown PayPal event type', ['type' => $eventType]);
        }

        return response('Webhook handled', 200);
    }

    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;
        
        if (!$orderId) {
            Log::error('No order ID found in Stripe payment intent metadata');
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::error('Order not found for Stripe payment', ['order_id' => $orderId]);
            return;
        }

        // Update payment transaction
        PaymentTransaction::updateOrCreate(
            [
                'order_id' => $order->id,
                'gateway' => 'stripe'
            ],
            [
                'status' => 'completed',
                'gateway_transaction_id' => $paymentIntent['id'],
                'gateway_response' => json_encode($paymentIntent),
                'completed_at' => now(),
                'amount' => $paymentIntent['amount'] / 100, // Convert from cents
                'currency' => strtoupper($paymentIntent['currency']),
            ]
        );

        // Update order status
        $order->update([
            'status' => Order::STATUS_PROCESSING,
            'payment_status' => 'completed',
            'payment_id' => $paymentIntent['id'],
        ]);

        // Process the order (deliver digital goods, etc.)
        $this->paymentService->processOrder($order);

        // Broadcast event
        event(new OrderStatusChanged($order, 'processing'));

        Log::info('Stripe payment completed', ['order_id' => $order->id]);
    }

    protected function handlePaymentIntentFailed($paymentIntent)
    {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;
        
        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            return;
        }

        PaymentTransaction::updateOrCreate(
            [
                'order_id' => $order->id,
                'gateway' => 'stripe'
            ],
            [
                'status' => 'failed',
                'gateway_response' => json_encode($paymentIntent),
                'failure_reason' => $paymentIntent['last_payment_error']['message'] ?? 'Payment failed',
            ]
        );

        $order->update([
            'status' => Order::STATUS_CANCELLED,
            'payment_status' => 'failed',
        ]);

        event(new OrderStatusChanged($order, 'cancelled'));

        Log::info('Stripe payment failed', ['order_id' => $order->id]);
    }

    protected function handleChargeDispute($dispute)
    {
        // Handle charge disputes
        Log::warning('Stripe charge dispute created', [
            'dispute_id' => $dispute['id'],
            'amount' => $dispute['amount'],
            'reason' => $dispute['reason'],
            'status' => $dispute['status']
        ]);
        
        // Find the order and mark it as disputed
        $chargeId = $dispute['charge'];
        $transaction = PaymentTransaction::where('gateway_transaction_id', $chargeId)->first();
        
        if ($transaction) {
            $transaction->update([
                'status' => 'disputed',
                'dispute_id' => $dispute['id'],
                'dispute_reason' => $dispute['reason'],
                'dispute_status' => $dispute['status'],
            ]);

            $transaction->order->update(['status' => Order::STATUS_DISPUTED]);
            
            // Notify admin about dispute
            // You can implement email notification here
        }
    }

    protected function handleInvoicePaymentSucceeded($invoice)
    {
        // Handle subscription payments
        Log::info('Stripe invoice payment succeeded', ['invoice_id' => $invoice['id']]);
    }

    protected function handleSubscriptionUpdated($subscription)
    {
        // Handle subscription updates
        Log::info('Stripe subscription updated', ['subscription_id' => $subscription['id']]);
    }

    protected function handlePayPalPaymentCompleted($payload)
    {
        $resource = $payload['resource'] ?? [];
        $orderId = $resource['custom_id'] ?? null;

        if (!$orderId) {
            Log::error('No order ID found in PayPal webhook');
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::error('Order not found for PayPal payment', ['order_id' => $orderId]);
            return;
        }

        PaymentTransaction::updateOrCreate(
            [
                'order_id' => $order->id,
                'gateway' => 'paypal'
            ],
            [
                'status' => 'completed',
                'gateway_transaction_id' => $resource['id'],
                'gateway_response' => json_encode($resource),
                'completed_at' => now(),
                'amount' => $resource['amount']['value'] ?? 0,
                'currency' => $resource['amount']['currency_code'] ?? 'USD',
            ]
        );

        $order->update([
            'status' => Order::STATUS_PROCESSING,
            'payment_status' => 'completed',
            'payment_id' => $resource['id'],
        ]);

        $this->paymentService->processOrder($order);
        event(new OrderStatusChanged($order, 'processing'));

        Log::info('PayPal payment completed', ['order_id' => $order->id]);
    }

    protected function handlePayPalPaymentDenied($payload)
    {
        $resource = $payload['resource'] ?? [];
        $orderId = $resource['custom_id'] ?? null;

        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            return;
        }

        PaymentTransaction::updateOrCreate(
            [
                'order_id' => $order->id,
                'gateway' => 'paypal'
            ],
            [
                'status' => 'failed',
                'gateway_response' => json_encode($resource),
                'failure_reason' => 'Payment denied by PayPal',
            ]
        );

        $order->update([
            'status' => Order::STATUS_CANCELLED,
            'payment_status' => 'failed',
        ]);

        event(new OrderStatusChanged($order, 'cancelled'));

        Log::info('PayPal payment denied', ['order_id' => $order->id]);
    }

    protected function handlePayPalPaymentRefunded($payload)
    {
        $resource = $payload['resource'] ?? [];
        $orderId = $resource['custom_id'] ?? null;

        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            return;
        }

        $order->update([
            'status' => Order::STATUS_REFUNDED,
            'payment_status' => 'refunded',
        ]);

        event(new OrderStatusChanged($order, 'refunded'));

        Log::info('PayPal payment refunded', ['order_id' => $order->id]);
    }

    protected function handlePayPalSubscriptionActivated($payload)
    {
        // Handle subscription activation
        Log::info('PayPal subscription activated', ['subscription_id' => $payload['resource']['id']]);
    }

    protected function verifyPayPalWebhook(Request $request)
    {
        // Implement PayPal webhook signature verification
        // This is a simplified version - implement proper verification according to PayPal docs
        $headers = $request->headers->all();
        $webhookId = config('services.paypal.webhook_id');
        
        // For now, return true - implement proper verification in production
        return true;
    }
}
