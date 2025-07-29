@extends('layouts.admin')

@section('title', __('Admin Dashboard'))

@section('content')
<div class="container-fluid py-4">
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle p-3">
                                <i class="fas fa-users text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Total Users') }}</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_users']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-circle p-3">
                                <i class="fas fa-shopping-bag text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Total Orders') }}</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_orders']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-circle p-3">
                                <i class="fas fa-dollar-sign text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Total Revenue') }}</h6>
                            <h3 class="mb-0">${{ number_format($stats['total_revenue'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-circle p-3">
                                <i class="fas fa-clock text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Pending Orders') }}</h6>
                            <h3 class="mb-0">{{ number_format($stats['pending_orders']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sales Chart -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">{{ __('Sales Overview') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Games -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">{{ __('Top Games') }}</h5>
                </div>
                <div class="card-body">
                    @foreach($topGames as $game)
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ $game->image_url }}" 
                             alt="{{ $game->name }}" 
                             class="rounded me-3" 
                             width="40" height="40">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $game->name }}</h6>
                            <small class="text-muted">{{ $game->orders_count }} {{ __('orders') }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('Recent Orders') }}</h5>
                    <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-outline-primary">
                        {{ __('View All') }}
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Order #') }}</th>
                                    <th>{{ __('Customer') }}</th>
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
                                        <span class="fw-bold">{{ $order->order_number }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $order->user->avatar_url }}" 
                                                 alt="{{ $order->user->name }}" 
                                                 class="rounded-circle me-2" 
                                                 width="32" height="32">
                                            <div>
                                                <div class="fw-bold">{{ $order->user->name }}</div>
                                                <small class="text-muted">{{ $order->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($order->service)
                                            <div>{{ $order->service->game->name }}</div>
                                            <small class="text-muted">{{ $order->service->name }}</small>
                                        @else
                                            <div>{{ $order->gameAccount->game->name }}</div>
                                            <small class="text-muted">{{ $order->gameAccount->title }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">${{ number_format($order->total_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->status_color }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $order->created_at->format('M d, Y') }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/admin.js'])
<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: @json($dailySales->pluck('date')),
        datasets: [{
            label: '{{ __("Daily Sales") }}',
            data: @json($dailySales->pluck('total')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '{{ __("Sales") }}: $' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endpush
