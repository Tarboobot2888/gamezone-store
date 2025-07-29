@extends('layouts.app')

@section('title', __('Dashboard') . ' - ' . config('app.name'))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h2">{{ __('Dashboard') }}</h1>
                <p class="text-muted">{{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ $orderStats['total'] }}</h3>
                                <p class="mb-0">{{ __('Total Orders') }}</p>
                            </div>
                            <div class="fs-1 opacity-50">📦</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ $orderStats['pending'] }}</h3>
                                <p class="mb-0">{{ __('Pending Orders') }}</p>
                            </div>
                            <div class="fs-1 opacity-50">⏳</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ $orderStats['completed'] }}</h3>
                                <p class="mb-0">{{ __('Completed Orders') }}</p>
                            </div>
                            <div class="fs-1 opacity-50">✅</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ $openTickets }}</h3>
                                <p class="mb-0">{{ __('Open Tickets') }}</p>
                            </div>
                            <div class="fs-1 opacity-50">🎫</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ __('Recent Orders') }}</h4>
                        <a href="{{ route('dashboard.orders') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
                    </div>
                    <div class="card-body">
                        @if($recentOrders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Order #') }}</th>
                                            <th>{{ __('Product') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentOrders as $order)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">{{ $order->order_number }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($order->service)
                                                        <img src="{{ $order->service->game->image_url }}" alt="" class="rounded me-2" width="32" height="32">
                                                        <div>
                                                            <div class="fw-semibold">{{ $order->service->name }}</div>
                                                            <small class="text-muted">{{ $order->service->game->name }}</small>
                                                        </div>
                                                    @elseif($order->gameAccount)
                                                        <img src="{{ $order->gameAccount->game->image_url }}" alt="" class="rounded me-2" width="32" height="32">
                                                        <div>
                                                            <div class="fw-semibold">{{ $order->gameAccount->title }}</div>
                                                            <small class="text-muted">{{ $order->gameAccount->game->name }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ $order->formatted_total }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $order->status_badge }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $order->created_at->format('M d, Y') }}</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewOrder({{ $order->id }})">
                                                    {{ __('View') }}
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <svg width="64" height="64" viewBox="0 0 64 64" class="text-muted">
                                        <circle cx="32" cy="32" r="28" fill="currentColor" opacity="0.1"/>
                                        <path d="M20 24h24v16H20z" fill="currentColor" opacity="0.3"/>
                                    </svg>
                                </div>
                                <h5 class="text-muted">{{ __('No orders yet') }}</h5>
                                <p class="text-muted">{{ __('Start shopping to see your orders here') }}</p>
                                <a href="{{ route('topup') }}" class="btn btn-primary">{{ __('Start Shopping') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewOrder(orderId) {
    // You can implement a modal or redirect to order details
    window.location.href = `/dashboard/orders/${orderId}`;
}
</script>
@endpush
@endsection
