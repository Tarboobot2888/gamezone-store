<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\GameAccount;
use App\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class PaymentService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create Stripe payment intent
     */
    public function createStripePaymentIntent(Order $order): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $order->total_amount * 100, // Convert to cents
                'currency' => strtolower($order->currency ?? 'usd'),
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ],
                'description' => "Order #{$order->order_number}",
                'receipt_email' => $order->user->email,
            ]);

            // Create payment transaction record
            PaymentTransaction::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'gateway' => 'stripe',
                'transaction_id' => $paymentIntent->id,
                'amount' => $order->total_amount,
                'currency' => $order->currency ?? 'USD',
                'status' => PaymentTransaction::STATUS_PENDING,
                'gateway_response' => $paymentIntent->toArray(),
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe payment intent creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create PayPal order
     */
    public function createPayPalOrder(Order $order): array
    {
        try {
            $accessToken = $this->getPayPalAccessToken();
            
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $order->order_number,
                        'amount' => [
                            'currency_code' => strtoupper($order->currency ?? 'USD'),
                            'value' => number_format($order->total_amount, 2, '.', ''),
                        ],
                        'description' => "Order #{$order->order_number}",
                        'custom_id' => $order->id,
                    ]
                ],
                'application_context' => [
                    'return_url' => route('payment.success', ['order_id' => $order->id]),
                    'cancel_url' => route('payment.cancelled', ['order_id' => $order->id]),
                ]
            ];

            $response = $this->makePayPalRequest('POST', '/v2/checkout/orders', $orderData, $accessToken);

            if ($response['success']) {
                // Create payment transaction record
                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'gateway' => 'paypal',
                    'transaction_id' => $response['data']['id'],
                    'amount' => $order->total_amount,
                    'currency' => $order->currency ?? 'USD',
                    'status' => PaymentTransaction::STATUS_PENDING,
                    'gateway_response' => $response['data'],
                ]);

                return [
                    'success' => true,
                    'order_id' => $response['data']['id'],
                    'approval_url' => collect($response['data']['links'])
                        ->firstWhere('rel', 'approve')['href'] ?? null,
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('PayPal order creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process order after successful payment
     */
    public function processOrder(Order $order): void
    {
        try {
            // Update order status
            $order->update([
                'status' => Order::STATUS_PROCESSING,
                'processed_at' => now(),
            ]);

            // Process each order item
            foreach ($order->items as $item) {
                if ($item->product_type === 'service') {
                    $this->processServiceOrder($item);
                } elseif ($item->product_type === 'account') {
                    $this->processAccountOrder($item);
                }
            }

            // Mark order as completed
            $order->update([
                'status' => Order::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            // Send confirmation email
            $this->sendOrderConfirmationEmail($order);

            event(new OrderStatusChanged($order, $order->status));

            Log::info('Order processed successfully', ['order_id' => $order->id]);

        } catch (\Exception $e) {
            Log::error('Order processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            $order->update([
                'status' => Order::STATUS_FAILED,
                'failure_reason' => $e->getMessage(),
            ]);

            event(new OrderStatusChanged($order, $order->status));
        }
    }

    /**
     * Process service order (top-up)
     */
    protected function processServiceOrder($orderItem): void
    {
        // For services, we typically need to:
        // 1. Call the game's API to add credits/items
        // 2. Update the order item with delivery status
        // 3. Store any delivery confirmation details

        $service = $orderItem->service;
        $customerDetails = $orderItem->customer_details;

        // This would integrate with actual game APIs
        // For now, we'll simulate instant delivery
        $orderItem->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'delivery_details' => [
                'method' => 'api',
                'player_id' => $customerDetails['player_id'] ?? null,
                'server' => $customerDetails['server'] ?? null,
                'delivered_amount' => $service->package_value,
                'delivered_type' => $service->package_type,
            ]
        ]);

        Log::info('Service order processed', [
            'order_item_id' => $orderItem->id,
            'service_id' => $service->id,
            'player_id' => $customerDetails['player_id'] ?? null,
        ]);
    }

    /**
     * Process account order
     */
    protected function processAccountOrder($orderItem): void
    {
        $account = $orderItem->account;

        // Mark account as sold
        $account->update([
            'status' => GameAccount::STATUS_SOLD,
            'sold_at' => now(),
            'buyer_id' => $orderItem->order->user_id,
        ]);

        // Update order item
        $orderItem->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'delivery_details' => [
                'account_id' => $account->id,
                'credentials_available' => true,
            ]
        ]);

        Log::info('Account order processed', [
            'order_item_id' => $orderItem->id,
            'account_id' => $account->id,
            'buyer_id' => $orderItem->order->user_id,
        ]);
    }

    /**
     * Send order confirmation email
     */
    protected function sendOrderConfirmationEmail(Order $order): void
    {
        try {
            Mail::to($order->user->email)->send(new \App\Mail\OrderConfirmation($order));
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get PayPal access token
     */
    protected function getPayPalAccessToken(): string
    {
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.client_secret');
        $baseUrl = config('services.paypal.mode') === 'live' 
            ? 'https://api.paypal.com' 
            : 'https://api.sandbox.paypal.com';

        $response = $this->makePayPalRequest('POST', '/v1/oauth2/token', [
            'grant_type' => 'client_credentials'
        ], null, [
            'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $clientSecret),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        if (!$response['success']) {
            throw new \Exception('Failed to get PayPal access token');
        }

        return $response['data']['access_token'];
    }

    /**
     * Make PayPal API request
     */
    protected function makePayPalRequest(string $method, string $endpoint, array $data = [], ?string $accessToken = null, array $headers = []): array
    {
        $baseUrl = config('services.paypal.mode') === 'live' 
            ? 'https://api.paypal.com' 
            : 'https://api.sandbox.paypal.com';

        $defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($accessToken) {
            $defaultHeaders['Authorization'] = 'Bearer ' . $accessToken;
        }

        $headers = array_merge($defaultHeaders, $headers);

        try {
            $client = new \GuzzleHttp\Client();
            
            $options = [
                'headers' => $headers,
                'timeout' => 30,
            ];

            if ($method === 'POST' || $method === 'PATCH') {
                if ($headers['Content-Type'] === 'application/x-www-form-urlencoded') {
                    $options['form_params'] = $data;
                } else {
                    $options['json'] = $data;
                }
            }

            $response = $client->request($method, $baseUrl . $endpoint, $options);
            
            return [
                'success' => true,
                'data' => json_decode($response->getBody()->getContents(), true),
                'status_code' => $response->getStatusCode(),
            ];

        } catch (\Exception $e) {
            Log::error('PayPal API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
