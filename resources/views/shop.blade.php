@extends('layouts.app')

@section('title', __('Shop Game Accounts') . ' - ' . config('app.name'))
@section('description', __('Browse premium game accounts with high levels and rare items'))

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Shop Accounts') }}</li>
                </ol>
            </nav>
            <h1 class="fw-bold mb-2">{{ __('Premium Game Accounts') }}</h1>
            <p class="text-muted">{{ __('High-level accounts with rare items and achievements') }}</p>
        </div>
    </div>

    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-filter me-2"></i>{{ __('Filters') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form id="filter-form" method="GET">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">{{ __('Search') }}</label>
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="{{ __('Search accounts...') }}"
                                   value="{{ request('search') }}">
                        </div>

                        <!-- Game Filter -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">{{ __('Game') }}</label>
                            <select name="game" class="form-select">
                                <option value="">{{ __('All Games') }}</option>
                                @foreach($games as $game)
                                <option value="{{ $game->id }}" {{ request('game') == $game->id ? 'selected' : '' }}>
                                    {{ $game->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">{{ __('Price Range') }}</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" 
                                           name="min_price" 
                                           class="form-control" 
                                           placeholder="{{ __('Min') }}"
                                           value="{{ request('min_price') }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" 
                                           name="max_price" 
                                           class="form-control" 
                                           placeholder="{{ __('Max') }}"
                                           value="{{ request('max_price') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Platform -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">{{ __('Platform') }}</label>
                            <select name="platform" class="form-select">
                                <option value="">{{ __('All Platforms') }}</option>
                                <option value="Android/iOS" {{ request('platform') == 'Android/iOS' ? 'selected' : '' }}>
                                    {{ __('Mobile') }}
                                </option>
                                <option value="PC" {{ request('platform') == 'PC' ? 'selected' : '' }}>
                                    {{ __('PC') }}
                                </option>
                                <option value="Console" {{ request('platform') == 'Console' ? 'selected' : '' }}>
                                    {{ __('Console') }}
                                </option>
                            </select>
                        </div>

                        <!-- Region -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">{{ __('Region') }}</label>
                            <select name="region" class="form-select">
                                <option value="">{{ __('All Regions') }}</option>
                                <option value="Global" {{ request('region') == 'Global' ? 'selected' : '' }}>
                                    {{ __('Global') }}
                                </option>
                                <option value="NA" {{ request('region') == 'NA' ? 'selected' : '' }}>
                                    {{ __('North America') }}
                                </option>
                                <option value="EU" {{ request('region') == 'EU' ? 'selected' : '' }}>
                                    {{ __('Europe') }}
                                </option>
                                <option value="ASIA" {{ request('region') == 'ASIA' ? 'selected' : '' }}>
                                    {{ __('Asia') }}
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>{{ __('Apply Filters') }}
                        </button>
                        <a href="{{ route('shop') }}" class="btn btn-outline-secondary w-100 mt-2">
                            {{ __('Clear Filters') }}
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Accounts Grid -->
        <div class="col-lg-9 col-md-8">
            <!-- Sort Options -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <span class="text-muted">
                        {{ __('Showing :from-:to of :total results', [
                            'from' => $accounts->firstItem() ?? 0,
                            'to' => $accounts->lastItem() ?? 0,
                            'total' => $accounts->total()
                        ]) }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <select name="sort" class="form-select form-select-sm" style="width: auto;" onchange="updateSort(this.value)">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>
                            {{ __('Newest First') }}
                        </option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>
                            {{ __('Price: Low to High') }}
                        </option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                            {{ __('Price: High to Low') }}
                        </option>
                        <option value="level_high" {{ request('sort') == 'level_high' ? 'selected' : '' }}>
                            {{ __('Highest Level') }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Accounts Grid -->
            @if($accounts->count() > 0)
            <div class="row g-4">
                @foreach($accounts as $account)
                <div class="col-lg-4 col-md-6">
                    <div class="card account-card h-100 border-0 shadow-sm"
                         x-data="{ hovered: false }"
                         @mouseenter="hovered = true"
                         @mouseleave="hovered = false">
                        <div class="position-relative overflow-hidden">
                            <img src="{{ $account->main_image }}" 
                                 class="card-img-top" 
                                 alt="{{ $account->title }}"
                                 :class="{ 'scale-105': hovered }"
                                 style="height: 200px; object-fit: cover; transition: transform 0.3s ease;">
                            
                            <!-- Badges -->
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-success">{{ $account->rank }}</span>
                            </div>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-warning text-dark">{{ __('Level') }} {{ $account->level }}</span>
                            </div>
                            
                            @if($account->is_featured)
                            <div class="position-absolute bottom-0 start-0 m-2">
                                <span class="badge bg-primary">{{ __('Featured') }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title fw-bold mb-0">{{ $account->game->name }}</h6>
                                <small class="text-muted">{{ $account->platform }}</small>
                            </div>
                            
                            <p class="card-text text-muted small mb-3">
                                {{ Str::limit($account->title, 60) }}
                            </p>

                            <!-- Key Specs -->
                            <div class="mb-3">
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="bg-light rounded p-2">
                                            <small class="text-muted d-block">{{ __('Level') }}</small>
                                            <strong>{{ $account->level }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="bg-light rounded p-2">
                                            <small class="text-muted d-block">{{ __('Rank') }}</small>
                                            <strong class="small">{{ $account->rank }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="bg-light rounded p-2">
                                            <small class="text-muted d-block">{{ __('Region') }}</small>
                                            <strong class="small">{{ $account->region }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-success fs-5">${{ number_format($account->price, 2) }}</span>
                                    <small class="text-muted d-block">{{ $account->currency }}</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('shop.account', $account) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        {{ __('Details') }}
                                    </a>
                                    <button type="button" 
                                            class="btn btn-primary btn-sm"
                                            onclick="addToCart('account', {{ $account->id }})">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $accounts->appends(request()->query())->links() }}
            </div>
            @else
            <!-- No Results -->
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">{{ __('No accounts found') }}</h4>
                <p class="text-muted">{{ __('Try adjusting your filters or search terms') }}</p>
                <a href="{{ route('shop') }}" class="btn btn-primary">
                    {{ __('View All Accounts') }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateSort(value) {
    const url = new URL(window.location);
    url.searchParams.set('sort', value);
    window.location.href = url.toString();
}

function addToCart(type, id) {
    axios.post('/api/v1/cart', {
        product_type: type,
        product_id: id,
        quantity: 1
    })
    .then(response => {
        showToast('{{ __("Item added to cart") }}', 'success');
        // Update cart count
        Alpine.store('app').cartCount = response.data.cart_count;
    })
    .catch(error => {
        showToast('{{ __("Error adding item to cart") }}', 'error');
    });
}
</script>
@endpush

@push('styles')
<style>
.account-card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
}

.scale-105 {
    transform: scale(1.05);
}
</style>
@endpush
