@extends('layouts.app')

@section('title', __('My Orders') . ' - ' . config('app.name'))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h2">{{ __('My Orders') }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Orders') }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row" x-data="ordersManager()">
            <!-- Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Filter Orders') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" x-model="filters.status" @change="loadOrders()">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="processing">{{ __('Processing') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                                <option value="cancelled">{{ __('Cancelled') }}</option>
                                <option value="refunded">{{ __('Refunded') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Product Type') }}</label>
                            <select class="form-select" x-model="filters.product_type" @change="loadOrders()">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="service">{{ __('Top-Up Service') }}</option>
                                <option value="account">{{ __('Game Account') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Date Range') }}</label>
                            <select class="form-select" x-model="filters.date_range" @change="loadOrders()">
                                <option value="">{{ __('All Time') }}</option>
                                <option value="today">{{ __('Today') }}</option>
                                <option value="week">{{ __('This Week') }}</option>
                                <option value="month">{{ __('This Month') }}</option>
                                <option value="year">{{ __('This Year') }}</option>
                            </select>
                        </div>

                        <button class="btn btn-outline-secondary w-100" @click="clearFilters()">
                            {{ __('Clear Filters') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Orders List -->
            <div class="col-lg-9">
                <!-- Loading State -->
                <div x-show="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                    </div>
                </div>

                <!-- Orders -->
                <div x-show="!loading">
                    <template x-for="order in orders" :key="order.id">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 fw-bold" x-text="'Order #' + order.order_number"></h6>
                                    <small class="text-muted" x-text="new Date(order.created_at).toLocaleDateString()"></small>
                                </div>
                                <div>
                                    <span class="badge" :class="getStatusBadgeClass(order.status)" x-text="order.status.charAt(0).toUpperCase() + order.status.slice(1)"></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img :src="order.product_image" :alt="order.product_name" class="img-fluid rounded" style="height: 80px; width: 80px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-1" x-text="order.product_name"></h6>
                                        <p class="text-muted mb-1" x-text="order.game_name"></p>
                                        <small class="text-muted" x-text="order.product_type === 'service' ? 'Top-Up Service' : 'Game Account'"></small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="fw-bold" x-text="'$' + parseFloat(order.total_amount).toFixed(2)"></div>
                                        <small class="text-muted" x-text="'Qty: ' + order.quantity"></small>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <div class="btn-group-vertical btn-group-sm">
                                            <button class="btn btn-outline-primary" @click="viewOrder(order.id)">
                                                {{ __('View Details') }}
                                            </button>
                                            <button class="btn btn-outline-info" @click="trackOrder(order.id)" x-show="order.status !== 'completed' && order.status !== 'cancelled'">
                                                {{ __('Track Order') }}
                                            </button>
                                            <button class="btn btn-outline-danger" @click="cancelOrder(order.id)" x-show="order.status === 'pending'">
                                                {{ __('Cancel') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Progress -->
                                <div class="mt-3" x-show="order.status !== 'cancelled' && order.status !== 'refunded'">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" :style="'width: ' + getProgressPercentage(order.status) + '%'"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">{{ __('Pending') }}</small>
                                        <small class="text-muted">{{ __('Processing') }}</small>
                                        <small class="text-muted">{{ __('Completed') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- No Orders -->
                    <div x-show="orders.length === 0" class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-shopping-bag fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted">{{ __('No orders found') }}</h5>
                        <p class="text-muted">{{ __('You haven\'t placed any orders yet.') }}</p>
                        <a href="{{ route('topup') }}" class="btn btn-primary">{{ __('Start Shopping') }}</a>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4" x-show="totalPages > 1">
                        <nav>
                            <ul class="pagination">
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <button class="page-link" @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
                                        {{ __('Previous') }}
                                    </button>
                                </li>
                                <template x-for="page in Array.from({length: totalPages}, (_, i) => i + 1)" :key="page">
                                    <li class="page-item" :class="{ active: page === currentPage }">
                                        <button class="page-link" @click="changePage(page)" x-text="page"></button>
                                    </li>
                                </template>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <button class="page-link" @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">
                                        {{ __('Next') }}
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function ordersManager() {
    return {
        orders: [],
        loading: true,
        currentPage: 1,
        totalPages: 1,
        filters: {
            status: '',
            product_type: '',
            date_range: ''
        },

        init() {
            this.loadOrders();
        },

        async loadOrders() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    ...this.filters
                });
                
                const response = await fetch(`/api/user/orders?${params}`);
                const data = await response.json();
                
                this.orders = data.data;
                this.totalPages = data.last_page;
            } catch (error) {
                console.error('Error loading orders:', error);
                showToast('{{ __("Error loading orders") }}', 'danger');
            } finally {
                this.loading = false;
            }
        },

        clearFilters() {
            this.filters = {
                status: '',
                product_type: '',
                date_range: ''
            };
            this.currentPage = 1;
            this.loadOrders();
        },

        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.loadOrders();
            }
        },

        viewOrder(orderId) {
            window.location.href = `/dashboard/orders/${orderId}`;
        },

        trackOrder(orderId) {
            window.location.href = `/orders/${orderId}/tracking`;
        },

        async cancelOrder(orderId) {
            if (confirm('{{ __("Are you sure you want to cancel this order?") }}')) {
                try {
                    const response = await fetch(`/api/orders/${orderId}/cancel`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast('{{ __("Order cancelled successfully") }}', 'success');
                        this.loadOrders();
                    } else {
                        showToast(data.message || '{{ __("Error cancelling order") }}', 'danger');
                    }
                } catch (error) {
                    console.error('Error cancelling order:', error);
                    showToast('{{ __("Error cancelling order") }}', 'danger');
                }
            }
        },

        getStatusBadgeClass(status) {
            const classes = {
                'pending': 'bg-warning',
                'processing': 'bg-info',
                'completed': 'bg-success',
                'cancelled': 'bg-danger',
                'refunded': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
        },

        getProgressPercentage(status) {
            const percentages = {
                'pending': 25,
                'processing': 75,
                'completed': 100,
                'cancelled': 0,
                'refunded': 0
            };
            return percentages[status] || 0;
        }
    }
}
</script>
@endpush
@endsection
