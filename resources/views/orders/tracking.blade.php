@extends('layouts.app')

@section('title', __('Order Tracking') . ' - ' . config('app.name'))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h2">{{ __('Order Tracking') }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Order Tracking') }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row" x-data="orderTracking('{{ $order->id }}')">
            <!-- Order Information -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ __('Order') }} #{{ $order->order_number }}</h4>
                        <span class="badge bg-{{ $order->status_badge }} fs-6" x-text="orderData.status">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <!-- Order Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold">{{ __('Order Information') }}</h6>
                                <p class="mb-1"><strong>{{ __('Order Date') }}:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                                <p class="mb-1"><strong>{{ __('Total Amount') }}:</strong> {{ $order->formatted_total }}</p>
                                <p class="mb-1"><strong>{{ __('Payment Method') }}:</strong> {{ ucfirst($order->payment_method ?? 'Pending') }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">{{ __('Product Information') }}</h6>
                                @if($order->service)
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $order->service->game->image_url }}" alt="{{ $order->service->name }}" class="rounded me-3" width="50" height="50">
                                        <div>
                                            <p class="mb-1"><strong>{{ $order->service->name }}</strong></p>
                                            <small class="text-muted">{{ $order->service->game->name }}</small>
                                        </div>
                                    </div>
                                @elseif($order->gameAccount)
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $order->gameAccount->main_image }}" alt="{{ $order->gameAccount->title }}" class="rounded me-3" width="50" height="50">
                                        <div>
                                            <p class="mb-1"><strong>{{ $order->gameAccount->title }}</strong></p>
                                            <small class="text-muted">{{ $order->gameAccount->game->name }}</small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Order Timeline -->
                        <div class="order-timeline">
                            <h6 class="fw-bold mb-3">{{ __('Order Progress') }}</h6>
                            <div class="timeline">
                                <div class="timeline-item" :class="getTimelineClass('pending')">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">{{ __('Order Placed') }}</h6>
                                        <p class="timeline-description">{{ __('Your order has been received and is being processed.') }}</p>
                                        <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>

                                <div class="timeline-item" :class="getTimelineClass('processing')">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">{{ __('Processing') }}</h6>
                                        <p class="timeline-description">{{ __('We are preparing your order for delivery.') }}</p>
                                        <small class="text-muted" x-show="orderData.processing_at" x-text="formatDate(orderData.processing_at)"></small>
                                    </div>
                                </div>

                                <div class="timeline-item" :class="getTimelineClass('completed')">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">{{ __('Completed') }}</h6>
                                        <p class="timeline-description">{{ __('Your order has been delivered successfully.') }}</p>
                                        <small class="text-muted" x-show="orderData.completed_at" x-text="formatDate(orderData.completed_at)"></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Details (for services) -->
                        @if($order->product_type === 'service' && $order->customer_details)
                        <div class="mt-4">
                            <h6 class="fw-bold">{{ __('Delivery Information') }}</h6>
                            <div class="bg-light p-3 rounded">
                                @if(isset($order->customer_details['player_id']))
                                    <p class="mb-1"><strong>{{ __('Player ID') }}:</strong> {{ $order->customer_details['player_id'] }}</p>
                                @endif
                                @if(isset($order->customer_details['server']))
                                    <p class="mb-1"><strong>{{ __('Server') }}:</strong> {{ $order->customer_details['server'] }}</p>
                                @endif
                                @if(isset($order->customer_details['notes']))
                                    <p class="mb-0"><strong>{{ __('Notes') }}:</strong> {{ $order->customer_details['notes'] }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Processing Details -->
                        <div x-show="orderData.processing_details" class="mt-4">
                            <h6 class="fw-bold">{{ __('Processing Details') }}</h6>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <div x-html="orderData.processing_details?.message || ''"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Actions -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('Order Actions') }}</h4>
                    </div>
                    <div class="card-body">
                        <!-- Real-time Status Updates -->
                        <div class="alert alert-info" x-show="orderData.status === 'processing'">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                <div>
                                    <strong>{{ __('Processing') }}</strong><br>
                                    <small>{{ __('Your order is being processed. You will receive updates shortly.') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success" x-show="orderData.status === 'completed'">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <div>
                                    <strong>{{ __('Completed') }}</strong><br>
                                    <small>{{ __('Your order has been delivered successfully!') }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" @click="refreshOrder()" :disabled="loading">
                                <span x-show="!loading">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    {{ __('Refresh Status') }}
                                </span>
                                <span x-show="loading">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    {{ __('Refreshing...') }}
                                </span>
                            </button>

                            <a href="{{ route('dashboard.orders') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i>
                                {{ __('View All Orders') }}
                            </a>

                            <button class="btn btn-outline-info" @click="contactSupport()">
                                <i class="fas fa-headset me-2"></i>
                                {{ __('Contact Support') }}
                            </button>
                        </div>

                        <!-- Estimated Delivery Time -->
                        <div class="mt-4" x-show="orderData.status !== 'completed'">
                            <h6 class="fw-bold">{{ __('Estimated Delivery') }}</h6>
                            <p class="text-muted">
                                @if($order->service)
                                    {{ $order->service->processing_time ?? '5-10 minutes' }}
                                @else
                                    {{ __('Within 24 hours') }}
                                @endif
                            </p>
                        </div>

                        <!-- Support Information -->
                        <div class="mt-4">
                            <h6 class="fw-bold">{{ __('Need Help?') }}</h6>
                            <p class="text-muted small">
                                {{ __('If you have any questions about your order, please contact our support team.') }}
                            </p>
                            <div class="d-flex gap-2">
                                <a href="{{ route('contact') }}" class="btn btn-sm btn-outline-primary">{{ __('Contact Us') }}</a>
                                <a href="{{ route('faq') }}" class="btn btn-sm btn-outline-secondary">{{ __('FAQ') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -1.5rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: #e2e8f0;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e2e8f0;
}

.timeline-item.active .timeline-marker {
    background: #6366f1;
    box-shadow: 0 0 0 2px #6366f1;
}

.timeline-item.completed .timeline-marker {
    background: #10b981;
    box-shadow: 0 0 0 2px #10b981;
}

.timeline-item.completed .timeline-marker::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 0.6rem;
    font-weight: bold;
}

.timeline-title {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.timeline-description {
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
    color: #64748b;
}
</style>
@endpush

@push('scripts')
<script>
function orderTracking(orderId) {
    return {
        orderId: orderId,
        orderData: @json($order),
        loading: false,
        
        init() {
            this.setupRealTimeUpdates();
        },
        
        async refreshOrder() {
            this.loading = true;
            
            try {
                const response = await fetch(`/api/v1/orders/${this.orderId}`);
                const data = await response.json();
                
                if (data.success) {
                    this.orderData = data.data;
                    showToast('{{ __("Order status updated") }}', 'success');
                } else {
                    showToast('{{ __("Error refreshing order") }}', 'danger');
                }
            } catch (error) {
                console.error('Error refreshing order:', error);
                showToast('{{ __("Error refreshing order") }}', 'danger');
            } finally {
                this.loading = false;
            }
        },
        
        getTimelineClass(status) {
            const currentStatus = this.orderData.status;
            const statusOrder = ['pending', 'processing', 'completed'];
            const currentIndex = statusOrder.indexOf(currentStatus);
            const statusIndex = statusOrder.indexOf(status);
            
            if (statusIndex < currentIndex) {
                return 'completed';
            } else if (statusIndex === currentIndex) {
                return 'active';
            }
            return '';
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleString();
        },
        
        contactSupport() {
            window.location.href = `{{ route('contact') }}?order=${this.orderData.order_number}`;
        },
        
        setupRealTimeUpdates() {
            if (window.Echo) {
                window.Echo.private(`order.${this.orderId}`)
                    .listen('OrderStatusUpdated', (e) => {
                        this.orderData = e.order;
                        showToast(`{{ __("Order status updated to") }} ${e.order.status}`, 'info');
                    });
            }
        }
    }
}
</script>
@endpush
@endsection
