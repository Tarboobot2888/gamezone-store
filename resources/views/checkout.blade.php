@extends('layouts.app')

@section('title', __('Checkout') . ' - ' . config('app.name'))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h2">{{ __('Checkout') }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('cart') }}">{{ __('Cart') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Checkout') }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row" x-data="checkoutManager({{ $order->id }})">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <!-- Order Items -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('Order Items') }}</h4>
                    </div>
                    <div class="card-body">
                        @if($order->service)
                            <div class="d-flex align-items-center">
                                <img src="{{ $order->service->game->image_url }}" alt="{{ $order->service->name }}" class="rounded me-3" width="60" height="60">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $order->service->name }}</h6>
                                    <small class="text-muted">{{ $order->service->game->name }}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-primary">{{ $order->service->package_value }} {{ $order->service->package_type }}</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">{{ $order->formatted_total }}</div>
                                    <small class="text-muted">{{ __('Qty') }}: {{ $order->quantity }}</small>
                                </div>
                            </div>
                        @elseif($order->gameAccount)
                            <div class="d-flex align-items-center">
                                <img src="{{ $order->gameAccount->main_image }}" alt="{{ $order->gameAccount->title }}" class="rounded me-3" width="60" height="60">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $order->gameAccount->title }}</h6>
                                    <small class="text-muted">{{ $order->gameAccount->game->name }}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-success">{{ $order->gameAccount->platform }}</span>
                                        <span class="badge bg-info">{{ $order->gameAccount->region }}</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">{{ $order->formatted_total }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Customer Information -->
                @if($order->product_type === 'service')
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('Customer Information') }}</h4>
                    </div>
                    <div class="card-body">
                        <form id="customer-form" @submit.prevent="updateCustomerInfo()">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Player ID/UID') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" x-model="customerInfo.player_id" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Server/Zone') }}</label>
                                    <input type="text" class="form-control" x-model="customerInfo.server">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Additional Notes') }}</label>
                                    <textarea class="form-control" rows="3" x-model="customerInfo.notes"></textarea>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-outline-primary" :disabled="loading">
                                    {{ __('Update Information') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Payment Method -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('Payment Method') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Stripe Payment -->
                            <div class="col-md-6">
                                <div class="payment-method" :class="paymentMethod === 'stripe' ? 'selected' : ''" @click="selectPaymentMethod('stripe')">
                                    <div class="d-flex align-items-center">
                                        <div class="payment-icon me-3">
                                            <svg width="40" height="24" viewBox="0 0 40 24" fill="none">
                                                <rect width="40" height="24" rx="4" fill="#635BFF"/>
                                                <path d="M8 12h24M8 8h16M8 16h20" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ __('Credit/Debit Card') }}</h6>
                                            <small class="text-muted">{{ __('Powered by Stripe') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PayPal Payment -->
                            <div class="col-md-6">
                                <div class="payment-method" :class="paymentMethod === 'paypal' ? 'selected' : ''" @click="selectPaymentMethod('paypal')">
                                    <div class="d-flex align-items-center">
                                        <div class="payment-icon me-3">
                                            <svg width="40" height="24" viewBox="0 0 40 24" fill="none">
                                                <rect width="40" height="24" rx="4" fill="#0070BA"/>
                                                <path d="M12 8h8c2 0 3 1 3 3s-1 3-3 3h-6l-1 2h-1zm8 0c1 0 2 .5 2 1.5S21 11 20 11h-6l1-3h5z" fill="white"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ __('PayPal') }}</h6>
                                            <small class="text-muted">{{ __('Pay with PayPal') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stripe Card Form -->
                        <div x-show="paymentMethod === 'stripe'" x-transition class="mt-4">
                            <div id="stripe-card-element" class="form-control" style="height: 40px; padding: 10px;"></div>
                            <div id="stripe-card-errors" class="text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('Order Summary') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('Order #') }}:</span>
                            <span class="fw-bold">{{ $order->order_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('Subtotal') }}:</span>
                            <span>{{ number_format($order->unit_price * $order->quantity, 2) }} {{ strtoupper($order->currency) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('Processing Fee') }}:</span>
                            <span>{{ number_format($order->total_amount - ($order->unit_price * $order->quantity), 2) }} {{ strtoupper($order->currency) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold mb-3">
                            <span>{{ __('Total') }}:</span>
                            <span class="text-primary">{{ $order->formatted_total }}</span>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __('Your order will be processed immediately after payment confirmation.') }}
                            </small>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-primary btn-lg" @click="processPayment()" :disabled="!paymentMethod || loading">
                                <span x-show="!loading">{{ __('Complete Payment') }}</span>
                                <span x-show="loading">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    {{ __('Processing...') }}
                                </span>
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>
                                {{ __('Secure payment powered by SSL encryption') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
function checkoutManager(orderId) {
    return {
        orderId: orderId,
        paymentMethod: null,
        customerInfo: {
            player_id: '{{ $order->customer_details["player_id"] ?? "" }}',
            server: '{{ $order->customer_details["server"] ?? "" }}',
            notes: '{{ $order->customer_details["notes"] ?? "" }}'
        },
        loading: false,
        stripe: null,
        cardElement: null,
        
        init() {
            this.initializeStripe();
        },
        
        initializeStripe() {
            this.stripe = Stripe('{{ config("services.stripe.key") }}');
            const elements = this.stripe.elements();
            
            this.cardElement = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#424770',
                        '::placeholder': {
                            color: '#aab7c4',
                        },
                    },
                },
            });
            
            this.cardElement.mount('#stripe-card-element');
            
            this.cardElement.on('change', (event) => {
                const displayError = document.getElementById('stripe-card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
        },
        
        selectPaymentMethod(method) {
            this.paymentMethod = method;
        },
        
        async updateCustomerInfo() {
            this.loading = true;
            
            try {
                const response = await fetch(`/api/orders/${this.orderId}/customer-info`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.customerInfo)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('{{ __("Customer information updated") }}', 'success');
                } else {
                    showToast(data.message || '{{ __("Error updating information") }}', 'danger');
                }
            } catch (error) {
                console.error('Error updating customer info:', error);
                showToast('{{ __("Error updating information") }}', 'danger');
            } finally {
                this.loading = false;
            }
        },
        
        async processPayment() {
            if (!this.paymentMethod) {
                showToast('{{ __("Please select a payment method") }}', 'warning');
                return;
            }
            
            this.loading = true;
            
            try {
                if (this.paymentMethod === 'stripe') {
                    await this.processStripePayment();
                } else if (this.paymentMethod === 'paypal') {
                    await this.processPayPalPayment();
                }
            } catch (error) {
                console.error('Payment error:', error);
                showToast('{{ __("Payment failed. Please try again.") }}', 'danger');
            } finally {
                this.loading = false;
            }
        },
        
        async processStripePayment() {
            const { token, error } = await this.stripe.createToken(this.cardElement);
            
            if (error) {
                showToast(error.message, 'danger');
                return;
            }
            
            const response = await fetch(`/api/payments/stripe`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    order_id: this.orderId,
                    token: token.id
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('{{ __("Payment successful!") }}', 'success');
                setTimeout(() => {
                    window.location.href = `/orders/${this.orderId}/success`;
                }, 2000);
            } else {
                showToast(data.message || '{{ __("Payment failed") }}', 'danger');
            }
        },
        
        async processPayPalPayment() {
            const response = await fetch(`/api/payments/paypal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    order_id: this.orderId
                })
            });
            
            const data = await response.json();
            
            if (data.success && data.approval_url) {
                window.location.href = data.approval_url;
            } else {
                showToast(data.message || '{{ __("PayPal payment failed") }}', 'danger');
            }
        }
    }
}
</script>

<style>
.payment-method {
    border: 2px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method:hover {
    border-color: #6366f1;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
}

.payment-method.selected {
    border-color: #6366f1;
    background-color: rgba(99, 102, 241, 0.05);
}

.payment-method.selected::after {
    content: '✓';
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #6366f1;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
}

.payment-method {
    position: relative;
}
</style>
@endpush
@endsection
