@extends('layouts.admin')

@section('title', __('Orders Management'))

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('Orders Management') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Orders') }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="exportOrders()">
                <i class="fas fa-download me-2"></i>{{ __('Export') }}
            </button>
            <a href="{{ route('admin.orders.analytics') }}" class="btn btn-outline-info">
                <i class="fas fa-chart-bar me-2"></i>{{ __('Analytics') }}
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle p-3">
                                <i class="fas fa-shopping-bag text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Total Orders') }}</h6>
                            <h3 class="mb-0">{{ number_format($orders->total()) }}</h3>
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
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Pending Orders') }}</h6>
                            <h3 class="mb-0">{{ $orders->where('status', 'pending')->count() }}</h3>
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
                                <i class="fas fa-check text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Completed') }}</h6>
                            <h3 class="mb-0">{{ $orders->where('status', 'completed')->count() }}</h3>
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
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Revenue') }}</h6>
                            <h3 class="mb-0">${{ number_format($orders->where('status', 'completed')->sum('total_amount'), 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="{{ __('Order number or customer...') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select class="form-select" name="status">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Date From') }}</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Date To') }}</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Orders') }} ({{ $orders->total() }})</h5>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-success" onclick="bulkUpdateStatus('completed')" id="bulkCompleteBtn" style="display: none;">
                    <i class="fas fa-check me-1"></i>{{ __('Mark Completed') }}
                </button>
                <button class="btn btn-outline-danger" onclick="bulkUpdateStatus('cancelled')" id="bulkCancelBtn" style="display: none;">
                    <i class="fas fa-times me-1"></i>{{ __('Cancel Selected') }}
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>{{ __('Order') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th width="150">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input order-checkbox" value="{{ $order->id }}">
                            </td>
                            <td>
                                <div class="fw-bold">#{{ $order->order_number }}</div>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $order->user->avatar_url }}" alt="{{ $order->user->name }}" class="rounded-circle me-2" width="32" height="32">
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
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><button class="dropdown-item" onclick="updateOrderStatus({{ $order->id }}, 'pending')">{{ __('Pending') }}</button></li>
                                            <li><button class="dropdown-item" onclick="updateOrderStatus({{ $order->id }}, 'processing')">{{ __('Processing') }}</button></li>
                                            <li><button class="dropdown-item" onclick="updateOrderStatus({{ $order->id }}, 'completed')">{{ __('Completed') }}</button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item text-danger" onclick="updateOrderStatus({{ $order->id }}, 'cancelled')">{{ __('Cancelled') }}</button></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-shopping-bag fa-2x mb-3"></i>
                                    <p>{{ __('No orders found') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

// Individual checkbox change
document.querySelectorAll('.order-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkActions);
});

function toggleBulkActions() {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    const bulkCompleteBtn = document.getElementById('bulkCompleteBtn');
    const bulkCancelBtn = document.getElementById('bulkCancelBtn');
    
    if (checkedBoxes.length > 0) {
        bulkCompleteBtn.style.display = 'inline-block';
        bulkCancelBtn.style.display = 'inline-block';
    } else {
        bulkCompleteBtn.style.display = 'none';
        bulkCancelBtn.style.display = 'none';
    }
}

// Update order status
async function updateOrderStatus(orderId, status) {
    try {
        const response = await fetch(`/admin/orders/${orderId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status: status })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        } else {
            showToast(data.message || '{{ __("Error updating order status") }}', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('{{ __("Error updating order status") }}', 'danger');
    }
}

// Bulk update status
async function bulkUpdateStatus(status) {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    const orderIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (orderIds.length === 0) {
        showToast('{{ __("Please select orders first") }}', 'warning');
        return;
    }
    
    if (await confirmAction(`{{ __("Update selected orders to") }} ${status}?`, '{{ __("Bulk Update") }}')) {
        try {
            const response = await fetch('/admin/orders/bulk-update-status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    orders: orderIds,
                    status: status 
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                location.reload();
            } else {
                showToast(data.message || '{{ __("Error updating orders") }}', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('{{ __("Error updating orders") }}', 'danger');
        }
    }
}

// Export orders
function exportOrders() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = '{{ route("admin.orders.export") }}?' + params.toString();
}
</script>
@endpush
@endsection
