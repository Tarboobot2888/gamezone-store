<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $paypalApiContext;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        
        $this->paypalApiContext = new ApiContext(
            new OAuthTokenCredential(
                config('services.paypal.client_id'),
                config('services.paypal.client_secret')
            )
        );
        
        $this->paypalApiContext->setConfig([
            'mode' => config('services.paypal.mode'),
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'ERROR'
        ]);
    }

    public function createStripePaymentIntent($amount, $orders)
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => 'usd',
                'metadata' => [
                    'order_ids' => implode(',', $orders->pluck('id')->toArray()),
                    'user_id' => $orders->first()->user_id,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return $paymentIntent;
        } catch (\Exception $e) {
            Log::error('Stripe payment intent creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createPayPalOrder($amount, $orders)
    {
        try {
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $items = [];
            foreach ($orders as $order) {
                $item = new Item();
                $item->setName($order->service ? $order->service->name : $order->gameAccount->title)
                     ->setCurrency('USD')
                     ->setQuantity($order->quantity)
                     ->setPrice($order->unit_price);
                $items[] = $item;
            }

            $itemList = new ItemList();
            $itemList->setItems($items);

            $details = new Details();
            $details->setSubtotal($amount);

            $amountObj = new Amount();
            $amountObj->setCurrency('USD')
                     ->setTotal($amount)
                     ->setDetails($details);

            $transaction = new Transaction();
            $transaction->setAmount($amountObj)
                       ->setItemList($itemList)
                       ->setDescription('GameZone Order Payment')
                       ->setInvoiceNumber('GZ-' . uniqid());

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl(config('app.frontend_url') . '/payment/success')
                        ->setCancelUrl(config('app.frontend_url') . '/payment/cancel');

            $payment = new Payment();
            $payment->setIntent('sale')
                   ->setPayer($payer)
                   ->setRedirectUrls($redirectUrls)
                   ->setTransactions([$transaction]);

            $payment->create($this->paypalApiContext);

            return [
                'id' => $payment->getId(),
                'links' => $payment->getLinks()
            ];
        } catch (\Exception $e) {
            Log::error('PayPal order creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function capturePayPalOrder($paymentId)
    {
        try {
            $payment = Payment::get($paymentId, $this->paypalApiContext);
            
            $execution = new PaymentExecution();
            $execution->setPayerId(request('PayerID'));

            $result = $payment->execute($execution, $this->paypalApiContext);

            return [
                'status' => $result->getState() === 'approved' ? 'COMPLETED' : 'FAILED',
                'purchase_units' => [[
                    'payments' => [
                        'captures' => [[
                            'amount' => [
                                'value' => $result->getTransactions()[0]->getAmount()->getTotal()
                            ]
                        ]]
                    ]
                ]]
            ];
        } catch (\Exception $e) {
            Log::error('PayPal order capture failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
