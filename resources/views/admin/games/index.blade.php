@extends('layouts.admin')

@section('title', __('Games Management'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ __('Games Management') }}</h1>
                    <p class="text-muted">{{ __('Manage games and their services') }}</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#gameModal" onclick="openCreateModal()">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Add Game') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Search') }}</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="{{ __('Search games...') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Category') }}</label>
                            <select class="form-select" name="category">
                                <option value="">{{ __('All Categories') }}</option>
                                <option value="Battle Royale" {{ request('category') === 'Battle Royale' ? 'selected' : '' }}>Battle Royale</option>
                                <option value="MOBA" {{ request('category') === 'MOBA' ? 'selected' : '' }}>MOBA</option>
                                <option value="RPG" {{ request('category') === 'RPG' ? 'selected' : '' }}>RPG</option>
                                <option value="FPS" {{ request('category') === 'FPS' ? 'selected' : '' }}>FPS</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Platform') }}</label>
                            <select class="form-select" name="platform">
                                <option value="">{{ __('All Platforms') }}</option>
                                <option value="Mobile" {{ request('platform') === 'Mobile' ? 'selected' : '' }}>Mobile</option>
                                <option value="PC" {{ request('platform') === 'PC' ? 'selected' : '' }}>PC</option>
                                <option value="Multi-platform" {{ request('platform') === 'Multi-platform' ? 'selected' : '' }}>Multi-platform</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" name="status">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-1"></i>
                                    {{ __('Filter') }}
                                </button>
                                <a href="{{ route('admin.games') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    {{ __('Clear') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Games Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="60">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                        </div>
                                    </th>
                                    <th>{{ __('Game') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Platform') }}</th>
                                    <th>{{ __('Services') }}</th>
                                    <th>{{ __('Accounts') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Sort Order') }}</th>
                                    <th width="120">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="sortable-table" data-model="games">
                                @forelse($games as $game)
                                <tr data-id="{{ $game->id }}">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $game->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $game->image_url }}" alt="{{ $game->name }}" class="rounded me-3" width="50" height="50">
                                            <div>
                                                <h6 class="mb-0">{{ $game->name }}</h6>
                                                <small class="text-muted">{{ $game->slug }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $game->category }}</span>
                                    </td>
                                    <td>{{ $game->platform }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $game->services_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $game->accounts_count }}</span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   {{ $game->is_active ? 'checked' : '' }}
                                                   onchange="toggleStatus('games', {{ $game->id }})">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="sort-handle me-2" style="cursor: move;">
                                                <i class="fas fa-grip-vertical text-muted"></i>
                                            </span>
                                            {{ $game->sort_order }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editGame({{ $game->id }})" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteGame({{ $game->id }})" title="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-gamepad fa-3x mb-3"></i>
                                            <p>{{ __('No games found') }}</p>
                                            <button class="btn btn-primary" onclick="openCreateModal()">
                                                {{ __('Add First Game') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($games->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $games->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Game Modal -->
<div class="modal fade" id="gameModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gameModalTitle">{{ __('Add Game') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="gameForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Game Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Slug') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="slug" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Category') }}</label>
                            <select class="form-select" name="category">
                                <option value="">{{ __('Select Category') }}</option>
                                <option value="Battle Royale">Battle Royale</option>
                                <option value="MOBA">MOBA</option>
                                <option value="RPG">RPG</option>
                                <option value="FPS">FPS</option>
                                <option value="Strategy">Strategy</option>
                                <option value="Sports">Sports</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Platform') }}</label>
                            <select class="form-select" name="platform">
                                <option value="">{{ __('Select Platform') }}</option>
                                <option value="Mobile">Mobile</option>
                                <option value="PC">PC</option>
                                <option value="Console">Console</option>
                                <option value="Multi-platform">Multi-platform</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Game Image') }}</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">{{ __('Recommended size: 300x300px') }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Banner Image') }}</label>
                            <input type="file" class="form-control" name="banner_image" accept="image/*">
                            <small class="text-muted">{{ __('Recommended size: 1200x400px') }}</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Sort Order') }}</label>
                            <input type="number" class="form-control" name="sort_order" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label">{{ __('Active') }}</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <hr>
                            <h6>{{ __('SEO Settings') }}</h6>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Meta Title') }}</label>
                            <input type="text" class="form-control" name="meta_title">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Meta Description') }}</label>
                            <textarea class="form-control" name="meta_description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">{{ __('Save Game') }}</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            {{ __('Saving...') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentGameId = null;

function openCreateModal() {
    currentGameId = null;
    document.getElementById('gameModalTitle').textContent = '{{ __("Add Game") }}';
    document.getElementById('gameForm').reset();
    
    // Generate slug from name
    document.querySelector('input[name="name"]').addEventListener('input', function() {
        const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        document.querySelector('input[name="slug"]').value = slug;
    });
}

async function editGame(gameId) {
    currentGameId = gameId;
    document.getElementById('gameModalTitle').textContent = '{{ __("Edit Game") }}';
    
    try {
        const response = await fetch(`/admin/games/${gameId}`);
        const data = await response.json();
        
        if (data.success) {
            const game = data.data;
            const form = document.getElementById('gameForm');
            
            form.querySelector('input[name="name"]').value = game.name;
            form.querySelector('input[name="slug"]').value = game.slug;
            form.querySelector('textarea[name="description"]').value = game.description || '';
            form.querySelector('select[name="category"]').value = game.category || '';
            form.querySelector('select[name="platform"]').value = game.platform || '';
            form.querySelector('input[name="sort_order"]').value = game.sort_order;
            form.querySelector('input[name="is_active"]').checked = game.is_active;
            form.querySelector('input[name="meta_title"]').value = game.meta_title || '';
            form.querySelector('textarea[name="meta_description"]').value = game.meta_description || '';
            
            new bootstrap.Modal(document.getElementById('gameModal')).show();
        }
    } catch (error) {
        console.error('Error loading game:', error);
        showToast('{{ __("Error loading game data") }}', 'danger');
    }
}

async function deleteGame(gameId) {
    const result = await confirmAction('{{ __("This will permanently delete the game and all its services. This action cannot be undone.") }}', '{{ __("Delete Game?") }}');
    
    if (result.isConfirmed) {
        try {
            const response = await fetch(`/admin/games/${gameId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('{{ __("Game deleted successfully") }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || '{{ __("Error deleting game") }}', 'danger');
            }
        } catch (error) {
            console.error('Error deleting game:', error);
            showToast('{{ __("Error deleting game") }}', 'danger');
        }
    }
}

async function toggleStatus(model, id) {
    try {
        const response = await fetch(`/admin/${model}/${id}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('{{ __("Status updated successfully") }}', 'success');
        } else {
            showToast(data.message || '{{ __("Error updating status") }}', 'danger');
            // Revert checkbox state
            event.target.checked = !event.target.checked;
        }
    } catch (error) {
        console.error('Error updating status:', error);
        showToast('{{ __("Error updating status") }}', 'danger');
        event.target.checked = !event.target.checked;
    }
}

// Handle form submission
document.getElementById('gameForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = currentGameId ? `/admin/games/${currentGameId}` : '/admin/games';
    const method = currentGameId ? 'PUT' : 'POST';
    
    if (currentGameId) {
        formData.append('_method', 'PUT');
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnSpinner = submitBtn.querySelector('.btn-spinner');
    
    btnText.classList.add('d-none');
    btnSpinner.classList.remove('d-none');
    submitBtn.disabled = true;
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message || '{{ __("Game saved successfully") }}', 'success');
            bootstrap.Modal.getInstance(document.getElementById('gameModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            if (data.errors) {
                displayFormErrors(this, data.errors);
            } else {
                showToast(data.message || '{{ __("Error saving game") }}', 'danger');
            }
        }
    } catch (error) {
        console.error('Error saving game:', error);
        showToast('{{ __("Error saving game") }}', 'danger');
    } finally {
        btnText.classList.remove('d-none');
        btnSpinner.classList.add('d-none');
        submitBtn.disabled = false;
    }
});

function displayFormErrors(form, errors) {
    // Clear previous errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    
    // Display new errors
    Object.keys(errors).forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
            input.classList.add('is-invalid');
            
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = errors[field][0];
            input.parentNode.appendChild(feedback);
        }
    });
}

// Bulk actions
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
@endsection
