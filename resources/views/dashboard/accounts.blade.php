@extends('layouts.app')

@section('title', __('My Accounts') . ' - ' . config('app.name'))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h2">{{ __('My Game Accounts') }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Accounts') }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row" x-data="accountsManager()">
            <!-- Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Filter Accounts') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Game') }}</label>
                            <select class="form-select" x-model="filters.game" @change="loadAccounts()">
                                <option value="">{{ __('All Games') }}</option>
                                <template x-for="game in games" :key="game.id">
                                    <option :value="game.id" x-text="game.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" x-model="filters.status" @change="loadAccounts()">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="active">{{ __('Active') }}</option>
                                <option value="suspended">{{ __('Suspended') }}</option>
                                <option value="banned">{{ __('Banned') }}</option>
                            </select>
                        </div>

                        <button class="btn btn-outline-secondary w-100" @click="clearFilters()">
                            {{ __('Clear Filters') }}
                        </button>
                    </div>
                </div>

                <!-- Account Stats -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Account Statistics') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="text-primary mb-0" x-text="stats.total_accounts"></h4>
                                    <small class="text-muted">{{ __('Total') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success mb-0" x-text="stats.active_accounts"></h4>
                                <small class="text-muted">{{ __('Active') }}</small>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <h4 class="text-info mb-0" x-text="'$' + parseFloat(stats.total_value).toFixed(2)"></h4>
                            <small class="text-muted">{{ __('Total Value') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accounts List -->
            <div class="col-lg-9">
                <!-- Loading State -->
                <div x-show="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                    </div>
                </div>

                <!-- Accounts Grid -->
                <div x-show="!loading">
                    <div class="row g-4">
                        <template x-for="account in accounts" :key="account.id">
                            <div class="col-md-6 col-xl-4">
                                <div class="card shadow-sm h-100">
                                    <div class="position-relative">
                                        <img :src="account.game_image" :alt="account.game_name" class="card-img-top" style="height: 200px; object-fit: cover;">
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge" :class="getStatusBadgeClass(account.status)" x-text="account.status.charAt(0).toUpperCase() + account.status.slice(1)"></span>
                                        </div>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title" x-text="account.title"></h5>
                                        <p class="text-muted mb-2" x-text="account.game_name"></p>
                                        <p class="card-text flex-grow-1" x-text="account.description"></p>
                                        
                                        <!-- Account Details -->
                                        <div class="mb-3">
                                            <div class="row g-2 text-center">
                                                <div class="col-4">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold" x-text="account.level || 'N/A'"></div>
                                                        <small class="text-muted">{{ __('Level') }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold" x-text="account.rank || 'N/A'"></div>
                                                        <small class="text-muted">{{ __('Rank') }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold" x-text="'$' + parseFloat(account.price).toFixed(2)"></div>
                                                        <small class="text-muted">{{ __('Paid') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button class="btn btn-primary flex-fill" @click="viewAccount(account.id)">
                                                {{ __('View Details') }}
                                            </button>
                                            <button class="btn btn-outline-success" @click="downloadCredentials(account.id)" x-show="account.credentials_available">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted">
                                        <small>{{ __('Purchased') }}: <span x-text="new Date(account.purchased_at).toLocaleDateString()"></span></small>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- No Accounts -->
                    <div x-show="accounts.length === 0" class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-gamepad fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted">{{ __('No accounts found') }}</h5>
                        <p class="text-muted">{{ __('You haven\'t purchased any game accounts yet.') }}</p>
                        <a href="{{ route('accounts') }}" class="btn btn-primary">{{ __('Browse Accounts') }}</a>
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

<!-- Account Details Modal -->
<div class="modal fade" id="accountDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" x-data="{ selectedAccount: null }">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Account Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" x-show="selectedAccount">
                <div class="row">
                    <div class="col-md-4">
                        <img :src="selectedAccount?.game_image" :alt="selectedAccount?.game_name" class="img-fluid rounded">
                    </div>
                    <div class="col-md-8">
                        <h4 x-text="selectedAccount?.title"></h4>
                        <p class="text-muted" x-text="selectedAccount?.game_name"></p>
                        <p x-text="selectedAccount?.description"></p>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <strong>{{ __('Level') }}:</strong> <span x-text="selectedAccount?.level || 'N/A'"></span>
                            </div>
                            <div class="col-6">
                                <strong>{{ __('Rank') }}:</strong> <span x-text="selectedAccount?.rank || 'N/A'"></span>
                            </div>
                            <div class="col-6">
                                <strong>{{ __('Status') }}:</strong> 
                                <span class="badge" :class="getStatusBadgeClass(selectedAccount?.status)" x-text="selectedAccount?.status"></span>
                            </div>
                            <div class="col-6">
                                <strong>{{ __('Purchase Price') }}:</strong> $<span x-text="parseFloat(selectedAccount?.price || 0).toFixed(2)"></span>
                            </div>
                        </div>

                        <div x-show="selectedAccount?.credentials_available" class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('Account credentials are available for download.') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-success" @click="downloadCredentials(selectedAccount?.id)" x-show="selectedAccount?.credentials_available">
                    <i class="fas fa-download me-2"></i>{{ __('Download Credentials') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function accountsManager() {
    return {
        accounts: [],
        games: [],
        loading: true,
        currentPage: 1,
        totalPages: 1,
        stats: {
            total_accounts: 0,
            active_accounts: 0,
            total_value: 0
        },
        filters: {
            game: '',
            status: ''
        },

        init() {
            this.loadGames();
            this.loadAccounts();
            this.loadStats();
        },

        async loadGames() {
            try {
                const response = await fetch('/api/games');
                const data = await response.json();
                this.games = data.data || data;
            } catch (error) {
                console.error('Error loading games:', error);
            }
        },

        async loadAccounts() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    ...this.filters
                });
                
                const response = await fetch(`/api/user/accounts?${params}`);
                const data = await response.json();
                
                this.accounts = data.data;
                this.totalPages = data.last_page;
            } catch (error) {
                console.error('Error loading accounts:', error);
                showToast('{{ __("Error loading accounts") }}', 'danger');
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                const response = await fetch('/api/user/account-stats');
                const data = await response.json();
                this.stats = data;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        clearFilters() {
            this.filters = {
                game: '',
                status: ''
            };
            this.currentPage = 1;
            this.loadAccounts();
        },

        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.loadAccounts();
            }
        },

        async viewAccount(accountId) {
            try {
                const response = await fetch(`/api/user/accounts/${accountId}`);
                const account = await response.json();
                
                // Set selected account for modal
                const modal = document.getElementById('accountDetailsModal');
                const modalInstance = new bootstrap.Modal(modal);
                
                // Update modal content
                modal.querySelector('[x-data]').__x.$data.selectedAccount = account;
                
                modalInstance.show();
            } catch (error) {
                console.error('Error loading account details:', error);
                showToast('{{ __("Error loading account details") }}', 'danger');
            }
        },

        async downloadCredentials(accountId) {
            try {
                const response = await fetch(`/api/user/accounts/${accountId}/credentials`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `account-${accountId}-credentials.txt`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    showToast('{{ __("Credentials downloaded successfully") }}', 'success');
                } else {
                    const data = await response.json();
                    showToast(data.message || '{{ __("Error downloading credentials") }}', 'danger');
                }
            } catch (error) {
                console.error('Error downloading credentials:', error);
                showToast('{{ __("Error downloading credentials") }}', 'danger');
            }
        },

        getStatusBadgeClass(status) {
            const classes = {
                'active': 'bg-success',
                'suspended': 'bg-warning',
                'banned': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        }
    }
}
</script>
@endpush
@endsection
