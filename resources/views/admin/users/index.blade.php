@extends('layouts.admin')

@section('title', __('Users Management'))

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('Users Management') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Users') }}</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="fas fa-plus me-2"></i>{{ __('Add User') }}
        </button>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="{{ __('Name or email...') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Role') }}</label>
                    <select class="form-select" name="role">
                        <option value="">{{ __('All Roles') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select class="form-select" name="status">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>{{ __('Verified') }}</option>
                        <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>{{ __('Unverified') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Users') }} ({{ $users->total() }})</h5>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" onclick="exportUsers()">
                    <i class="fas fa-download me-1"></i>{{ __('Export') }}
                </button>
                <button class="btn btn-outline-danger" onclick="bulkAction('delete')" id="bulkDeleteBtn" style="display: none;">
                    <i class="fas fa-trash me-1"></i>{{ __('Delete Selected') }}
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
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Orders') }}</th>
                            <th>{{ __('Joined') }}</th>
                            <th width="150">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle me-3" width="40" height="40">
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($user->email_verified_at)
                                    <span class="badge bg-success">{{ __('Verified') }}</span>
                                @else
                                    <span class="badge bg-warning">{{ __('Unverified') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold">{{ $user->orders_count ?? 0 }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $user->created_at->format('M d, Y') }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary" onclick="editUser({{ $user->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if(!$user->hasRole('super-admin'))
                                    <button class="btn btn-outline-danger" onclick="deleteUser({{ $user->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-2x mb-3"></i>
                                    <p>{{ __('No users found') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add New User') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createUserForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Full Name') }}</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Email Address') }}</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Password') }}</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Role') }}</label>
                        <select class="form-select" name="role" required>
                            <option value="">{{ __('Select Role') }}</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="email_verified" id="emailVerified">
                        <label class="form-check-label" for="emailVerified">
                            {{ __('Email Verified') }}
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create User') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit User') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Full Name') }}</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Email Address') }}</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Password') }} <small class="text-muted">({{ __('leave blank to keep current') }})</small></label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Role') }}</label>
                        <select class="form-select" name="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="email_verified" id="editEmailVerified">
                        <label class="form-check-label" for="editEmailVerified">
                            {{ __('Email Verified') }}
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Update User') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

// Individual checkbox change
document.querySelectorAll('.user-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkActions);
});

function toggleBulkActions() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    if (checkedBoxes.length > 0) {
        bulkDeleteBtn.style.display = 'inline-block';
    } else {
        bulkDeleteBtn.style.display = 'none';
    }
}

// Create user form
document.getElementById('createUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("admin.users.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
            location.reload();
        } else {
            showToast(data.message || '{{ __("Error creating user") }}', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('{{ __("Error creating user") }}', 'danger');
    }
});

// Edit user
async function editUser(userId) {
    try {
        const response = await fetch(`/admin/users/${userId}`);
        const user = await response.json();
        
        const form = document.getElementById('editUserForm');
        form.querySelector('[name="user_id"]').value = user.id;
        form.querySelector('[name="name"]').value = user.name;
        form.querySelector('[name="email"]').value = user.email;
        form.querySelector('[name="role"]').value = user.roles[0]?.name || '';
        form.querySelector('[name="email_verified"]').checked = !!user.email_verified_at;
        
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    } catch (error) {
        console.error('Error:', error);
        showToast('{{ __("Error loading user data") }}', 'danger');
    }
}

// Update user form
document.getElementById('editUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const userId = formData.get('user_id');
    
    try {
        const response = await fetch(`/admin/users/${userId}`, {
            method: 'PATCH',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            location.reload();
        } else {
            showToast(data.message || '{{ __("Error updating user") }}', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('{{ __("Error updating user") }}', 'danger');
    }
});

// Delete user
async function deleteUser(userId) {
    if (await confirmAction('{{ __("This action cannot be undone.") }}', '{{ __("Delete this user?") }}')) {
        try {
            const response = await fetch(`/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                location.reload();
            } else {
                showToast(data.message || '{{ __("Error deleting user") }}', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('{{ __("Error deleting user") }}', 'danger');
        }
    }
}

// Bulk actions
async function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (userIds.length === 0) {
        showToast('{{ __("Please select users first") }}', 'warning');
        return;
    }
    
    if (action === 'delete') {
        if (await confirmAction('{{ __("This action cannot be undone.") }}', '{{ __("Delete selected users?") }}')) {
            // Implement bulk delete
            showToast('{{ __("Bulk delete feature coming soon") }}', 'info');
        }
    }
}

// Export users
function exportUsers() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = '{{ route("admin.users.index") }}?' + params.toString();
}
</script>
@endpush
@endsection
