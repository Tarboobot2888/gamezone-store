@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold mb-3">{{ __('messages.game_topup') }}</h1>
            <p class="lead text-muted">{{ __('messages.topup_subtitle') }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4" x-data="topupFilters()">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.search_games') }}</label>
                            <input type="text" 
                                   class="form-control" 
                                   placeholder="{{ __('messages.search_placeholder') }}"
                                   x-model="filters.search"
                                   @input.debounce.500ms="applyFilters()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.game') }}</label>
                            <select class="form-select" x-model="filters.game" @change="applyFilters()">
                                <option value="">{{ __('messages.all_games') }}</option>
                                <template x-for="game in games" :key="game.id">
                                    <option :value="game.slug" x-text="game.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.price_range') }}</label>
                            <select class="form-select" x-model="filters.priceRange" @change="applyFilters()">
                                <option value="">{{ __('messages.any_price') }}</option>
                                <option value="0-10">$0 - $10</option>
                                <option value="10-25">$10 - $25</option>
                                <option value="25-50">$25 - $50</option>
                                <option value="50-100">$50 - $100</option>
                                <option value="100+">$100+</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('messages.sort_by') }}</label>
                            <select class="form-select" x-model="filters.sort" @change="applyFilters()">
                                <option value="popular">{{ __('messages.most_popular') }}</option>
                                <option value="price_low">{{ __('messages.price_low_to_high') }}</option>
                                <option value="price_high">{{ __('messages.price_high_to_low') }}</option>
                                <option value="newest">{{ __('messages.newest_first') }}</option>
                                <option value="name">{{ __('messages.alphabetical') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Grid -->
    <div class="row" id="services-grid" x-data="servicesGrid()">
        <!-- Loading State -->
        <div x-show="loading" class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('messages.loading') }}</span>
            </div>
            <p class="mt-3 text-muted">{{ __('messages.loading_services') }}</p>
        </div>

        <!-- Services -->
        <template x-for="service in services" :key="service.id">
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card service-card h-100 border-0 shadow-sm">
                    <div class="position-relative overflow-hidden">
                        <img :src="service.image || service.game?.image || '/images/services/default.jpg'" 
                             class="card-img-top" 
                             :alt="service.name" 
                             style="height: 200px; object-fit: cover;">
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-primary" x-text="service.game?.name"></span>
                        </div>
                        <div x-show="service.discount_percentage" class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-danger" x-text="`-${service.discount_percentage}%`"></span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title fw-bold" x-text="service.name"></h6>
                        <p class="card-text text-muted small flex-grow-1" x-text="service.description?.substring(0, 80) + '...'"></p>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span x-show="service.original_price && service.original_price > service.price" 
                                          class="text-muted text-decoration-line-through small" 
                                          x-text="`$${service.original_price}`"></span>
                                    <span class="fw-bold text-primary fs-5" x-text="`$${service.price}`"></span>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> {{ __('messages.instant_delivery') }}
                                </small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" @click="addToCart(service)">
                                    <i class="fas fa-shopping-cart me-2"></i>{{ __('messages.add_to_cart') }}
                                </button>
                                <button class="btn btn-outline-primary btn-sm" @click="buyNow(service)">
                                    {{ __('messages.buy_now') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <div x-show="!loading && services.length === 0" class="col-12 text-center py-5">
            <div class="mb-4">
                <i class="fas fa-search fa-3x text-muted"></i>
            </div>
            <h4 class="text-muted">{{ __('messages.no_services_found') }}</h4>
            <p class="text-muted">{{ __('messages.try_different_filters') }}</p>
        </div>
    </div>

    <!-- Pagination -->
    <div class="row mt-5" x-data="pagination()">
        <div class="col-12">
            <nav x-show="totalPages > 1">
                <ul class="pagination justify-content-center">
                    <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                        <button class="page-link" @click="goToPage(currentPage - 1)" :disabled="currentPage === 1">
                            {{ __('messages.previous') }}
                        </button>
                    </li>
                    
                    <template x-for="page in visiblePages" :key="page">
                        <li class="page-item" :class="{ 'active': page === currentPage }">
                            <button class="page-link" @click="goToPage(page)" x-text="page"></button>
                        </li>
                    </template>
                    
                    <li class="page-item" :class="{ 'disabled': currentPage === totalPages }">
                        <button class="page-link" @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages">
                            {{ __('messages.next') }}
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function topupFilters() {
    return {
        filters: {
            search: new URLSearchParams(window.location.search).get('search') || '',
            game: new URLSearchParams(window.location.search).get('game') || '',
            priceRange: '',
            sort: 'popular'
        },
        games: [],

        async init() {
            await this.loadGames();
            this.applyFilters();
        },

        async loadGames() {
            try {
                const response = await fetch('/api/v1/games');
                const data = await response.json();
                if (data.success) {
                    this.games = data.data.data || data.data;
                }
            } catch (error) {
                console.error('Error loading games:', error);
            }
        },

        applyFilters() {
            // Update URL
            const params = new URLSearchParams();
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    params.set(key, this.filters[key]);
                }
            });
            
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.replaceState({}, '', newUrl);

            // Trigger services reload
            window.dispatchEvent(new CustomEvent('filters-changed', { detail: this.filters }));
        }
    }
}

function servicesGrid() {
    return {
        services: [],
        loading: true,
        currentFilters: {},

        async init() {
            window.addEventListener('filters-changed', (e) => {
                this.currentFilters = e.detail;
                this.loadServices();
            });

            // Load initial services
            await this.loadServices();
        },

        async loadServices() {
            this.loading = true;
            
            try {
                const params = new URLSearchParams(this.currentFilters);
                const response = await fetch(`/api/v1/services?${params.toString()}`);
                const data = await response.json();
                
                if (data.success) {
                    this.services = data.data.data || data.data;
                    
                    // Update pagination
                    window.dispatchEvent(new CustomEvent('pagination-update', { 
                        detail: {
                            currentPage: data.data.current_page || 1,
                            totalPages: data.data.last_page || 1,
                            total: data.data.total || 0
                        }
                    }));
                }
            } catch (error) {
                console.error('Error loading services:', error);
                showToast('{{ __("messages.error_loading_services") }}', 'danger');
            } finally {
                this.loading = false;
            }
        },

        async addToCart(service) {
            try {
                const response = await fetch('/api/v1/cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        product_type: 'service',
                        product_id: service.id,
                        quantity: 1
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showToast('{{ __("messages.item_added_to_cart") }}', 'success');
                    updateCartCount();
                } else {
                    showToast(data.message || '{{ __("messages.error_adding_to_cart") }}', 'danger');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                showToast('{{ __("messages.error_adding_to_cart") }}', 'danger');
            }
        },

        async buyNow(service) {
            await this.addToCart(service);
            window.location.href = '/cart';
        }
    }
}

function pagination() {
    return {
        currentPage: 1,
        totalPages: 1,
        total: 0,

        get visiblePages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            return pages;
        },

        init() {
            window.addEventListener('pagination-update', (e) => {
                this.currentPage = e.detail.currentPage;
                this.totalPages = e.detail.totalPages;
                this.total = e.detail.total;
            });
        },

        goToPage(page) {
            if (page < 1 || page > this.totalPages || page === this.currentPage) {
                return;
            }
            
            this.currentPage = page;
            
            // Update URL and trigger reload
            const params = new URLSearchParams(window.location.search);
            params.set('page', page);
            
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.replaceState({}, '', newUrl);
            
            window.dispatchEvent(new CustomEvent('filters-changed', { 
                detail: Object.fromEntries(params.entries()) 
            }));
        }
    }
}
</script>
@endpush
