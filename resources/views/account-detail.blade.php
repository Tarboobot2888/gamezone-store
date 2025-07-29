@extends('layouts.app')

@section('title', $account->title . ' - ' . config('app.name'))
@section('description', Str::limit($account->description, 160))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('accounts') }}">{{ __('Accounts') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $account->title }}</li>
            </ol>
        </nav>

        <div class="row" x-data="accountDetail({{ $account->id }})">
            <!-- Account Images -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <!-- Main Image -->
                        <div class="position-relative">
                            <img :src="selectedImage" alt="{{ $account->title }}" class="img-fluid w-100 rounded-top" style="height: 400px; object-fit: cover;">
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-success fs-6">{{ $account->rank }}</span>
                            </div>
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-warning text-dark fs-6">{{ __('Level') }} {{ $account->level }}</span>
                            </div>
                            @if(!$account->is_available)
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <span class="badge bg-danger fs-3 p-3">{{ __('SOLD') }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Thumbnail Gallery -->
                        <div class="p-3">
                            <div class="row g-2">
                                @foreach($account->images as $image)
                                <div class="col-3">
                                    <img src="{{ $image }}" 
                                         alt="Account Image" 
                                         class="img-fluid rounded cursor-pointer thumbnail"
                                         :class="{ 'border border-primary border-3': selectedImage === '{{ $image }}' }"
                                         @click="selectedImage = '{{ $image }}'"
                                         style="height: 80px; object-fit: cover;">
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="h3 fw-bold mb-3">{{ $account->title }}</h1>
                        
                        <!-- Price and Availability -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <span class="h2 text-success fw-bold">${{ number_format($account->price, 2) }}</span>
                                <small class="text-muted ms-2">{{ strtoupper($account->currency) }}</small>
                            </div>
                            <div>
                                @if($account->is_available)
                                    <span class="badge bg-success fs-6">{{ __('Available') }}</span>
                                @else
                                    <span class="badge bg-danger fs-6">{{ __('Sold Out') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Game Info -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $account->game->image_url }}" alt="{{ $account->game->name }}" class="rounded me-3" width="50" height="50">
                                    <div>
                                        <h6 class="mb-0">{{ $account->game->name }}</h6>
                                        <small class="text-muted">{{ $account->platform }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 text-end">
                                <div>
                                    <small class="text-muted d-block">{{ __('Region') }}</small>
                                    <strong>{{ $account->region }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <h5 class="fw-bold">{{ __('Description') }}</h5>
                            <p class="text-muted">{{ $account->description }}</p>
                        </div>

                        <!-- Specifications -->
                        <div class="mb-4">
                            <h5 class="fw-bold">{{ __('Account Specifications') }}</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    @foreach($account->specifications as $key => $value)
                                    <tr>
                                        <td class="fw-semibold">{{ $key }}</td>
                                        <td>{{ $value }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <!-- Purchase Actions -->
                        @if($account->is_available)
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-lg" @click="addToCart()" :disabled="loading">
                                <span x-show="!loading">
                                    <i class="fas fa-shopping-cart me-2"></i>{{ __('Add to Cart') }}
                                </span>
                                <span x-show="loading">
                                    <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Adding...') }}
                                </span>
                            </button>
                            <button class="btn btn-primary btn-lg" @click="buyNow()" :disabled="loading">
                                <i class="fas fa-bolt me-2"></i>{{ __('Buy Now') }}
                            </button>
                        </div>
                        @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ __('This account has been sold and is no longer available.') }}
                        </div>
                        @endif

                        <!-- Security Notice -->
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>{{ __('Security Notice:') }}</strong>
                            {{ __('All accounts are verified and come with a 7-day warranty against previous owner issues.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Accounts -->
        @if($relatedAccounts->count() > 0)
        <section class="mt-5">
            <h3 class="fw-bold mb-4">{{ __('Related Accounts') }}</h3>
            <div class="row g-4">
                @foreach($relatedAccounts as $related)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card h-100 shadow-sm">
                        <img src="{{ $related->main_image }}" class="card-img-top" alt="{{ $related->title }}" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">{{ $related->title }}</h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-success">${{ number_format($related->price, 2) }}</span>
                                <a href="{{ route('account.detail', $related) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('View') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>

@push('scripts')
<script>
function accountDetail(accountId) {
    return {
        selectedImage: '{{ $account->main_image }}',
        loading: false,

        async addToCart() {
            this.loading = true;
            try {
                const response = await fetch('/api/cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        product_type: 'account',
                        product_id: accountId,
                        quantity: 1
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showToast('{{ __("Account added to cart") }}', 'success');
                    // Update cart count in navbar
                    Alpine.store('app').cartCount++;
                } else {
                    showToast(data.message || '{{ __("Error adding to cart") }}', 'danger');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                showToast('{{ __("Error adding to cart") }}', 'danger');
            } finally {
                this.loading = false;
            }
        },

        async buyNow() {
            this.loading = true;
            try {
                const response = await fetch('/api/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        product_type: 'account',
                        product_id: accountId,
                        quantity: 1
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = `/checkout/${data.order.id}`;
                } else {
                    showToast(data.message || '{{ __("Error creating order") }}', 'danger');
                }
            } catch (error) {
                console.error('Error creating order:', error);
                showToast('{{ __("Error creating order") }}', 'danger');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush

@push('styles')
<style>
.thumbnail {
    cursor: pointer;
    transition: all 0.3s ease;
}

.thumbnail:hover {
    transform: scale(1.05);
}
</style>
@endpush
@endsection
