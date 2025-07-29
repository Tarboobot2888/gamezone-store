@extends('layouts.app')

@section('title', __('Game Accounts') . ' - ' . config('app.name'))
@section('description', __('Browse premium game accounts for sale. High-level accounts with rare items and achievements.'))

@section('content')
<div class="pt-5 mt-4">
    <!-- Page Header -->
    <section class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="h2 mb-0">{{ __('Game Accounts') }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50">{{ __('Home') }}</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">{{ __('Accounts') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters and Search -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="row" x-data="accountFilters()">
                <div class="col-lg-3 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
                        </div>
                        <div class="card-body">
                            <!-- Game Filter -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('Game') }}</label>
                                <select class="form-select" x-model="filters.game" @change="applyFilters()">
                                    <option value="">{{ __('All Games') }}</option>
                                    @foreach($games as $game)
                                        <option value="{{ $game->id }}">{{ $game->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Platform Filter -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('Platform') }}</label>
                                <select class="form-select" x-model="filters.platform" @change="applyFilters()">
                                    <option value="">{{ __('All Platforms') }}</option>
                                    <option value="PC">PC</option>
                                    <option value="Mobile">Mobile</option>
                                    <option value="Console">Console</option>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('Price Range') }}</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" placeholder="{{ __('Min') }}" x-model="filters.min_price" @input="applyFilters()">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" placeholder="{{ __('Max') }}" x-model="filters.max_price" @input="applyFilters()">
                                    </div>
                                </div>
                            </div>

                            <!-- Region Filter -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ __('Region') }}</label>
                                <select class="form-select" x-model="filters.region" @change="applyFilters()">
                                    <option value="">{{ __('All Regions') }}</option>
                                    <option value="Global">Global</option>
                                    <option value="NA">North America</option>
                                    <option value="EU">Europe</option>
                                    <option value="ASIA">Asia</option>
                                </select>
                            </div>

                            <!-- Clear Filters -->
                            <button class="btn btn-outline-secondary w-100" @click="clearFilters()">
                                {{ __('Clear Filters') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <!-- Search and Sort -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="{{ __('Search accounts...') }}" x-model="filters.search" @input="applyFilters()">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" x-model="filters.sort" @change="applyFilters()">
                                <option value="newest">{{ __('Newest First') }}</option>
                                <option value="price_low">{{ __('Price: Low to High') }}</option>
                                <option value="price_high">{{ __('Price: High to Low') }}</option>
                                <option value="popular">{{ __('Most Popular') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div x-show="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                        </div>
                    </div>

                    <!-- Accounts Grid -->
                    <div class="row g-4" x-show="!loading">
                        <template x-for="account in accounts" :key="account.id">
                            <div class="col-lg-4 col-md-6">
                                <div class="card account-card h-100 shadow-sm border-0">
                                    <div class="position-relative">
                                        <img :src="account.main_image" :alt="account.title" class="card-img-top" style="height: 250px; object-fit: cover;">
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-success" x-text="account.rank"></span>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-warning text-dark" x-text="'Level ' + account.level"></span>
                                        </div>
                                        <div class="position-absolute bottom-0 end-0 m-2" x-show="!account.is_available">
                                            <span class="badge bg-danger">{{ __('Sold') }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold" x-text="account.title"></h6>
                                        <p class="card-text text-muted small" x-text="account.description.substring(0, 100) + '...'"></p>
                                        
                                        <div class="row text-center mb-3">
                                            <div class="col-4">
                                                <small class="text-muted d-block">{{ __('Game') }}</small>
                                                <strong x-text="account.game.name"></strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">{{ __('Platform') }}</small>
                                                <strong x-text="account.platform"></strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">{{ __('Region') }}</small>
                                                <strong x-text="account.region"></strong>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 text-success mb-0" x-text="'$' + parseFloat(account.price).toFixed(2)"></span>
                                            <a :href="'/account/' + account.id" class="btn btn-success btn-sm" x-show="account.is_available">
                                                {{ __('View Details') }}
                                            </a>
                                            <span class="btn btn-secondary btn-sm disabled" x-show="!account.is_available">
                                                {{ __('Sold Out') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- No Results -->
                    <div x-show="!loading && accounts.length === 0" class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-search fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted">{{ __('No accounts found') }}</h5>
                        <p class="text-muted">{{ __('Try adjusting your filters or search terms') }}</p>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4" x-show="!loading && totalPages > 1">
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
    </section>
</div>

@push('scripts')
<script>
function accountFilters() {
    return {
        accounts: [],
        loading: true,
        currentPage: 1,
        totalPages: 1,
        filters: {
            game: '',
            platform: '',
            min_price: '',
            max_price: '',
            region: '',
            search: '',
            sort: 'newest'
        },

        init() {
            this.loadAccounts();
        },

        async loadAccounts() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    ...this.filters
                });
                
                const response = await fetch(`/api/accounts?${params}`);
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

        applyFilters() {
            this.currentPage = 1;
            this.loadAccounts();
        },

        clearFilters() {
            this.filters = {
                game: '',
                platform: '',
                min_price: '',
                max_price: '',
                region: '',
                search: '',
                sort: 'newest'
            };
            this.applyFilters();
        },

        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.loadAccounts();
            }
        }
    }
}
</script>
@endpush

@push('styles')
<style>
.account-card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
}

.account-card .card-img-top {
    transition: transform 0.3s ease;
}

.account-card:hover .card-img-top {
    transform: scale(1.05);
}
</style>
@endpush
@endsection
