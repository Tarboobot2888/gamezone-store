@extends('layouts.app')

@section('content')
<div class="container py-5" x-data="cartManager()">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 fw-bold">{{ __('messages.shopping_cart') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('messages.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('messages.cart') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ __('messages.loading') }}</span>
        </div>
        <p class="mt-3 text-muted">{{ __('messages.loading_cart') }}</p>
    </div>

    <!-- Empty Cart -->
    <div x-show="!loading && items.length === 0" class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-shopping-cart fa-4x text-muted"></i>
        </div>
        <h3 class="text-muted mb-3">{{ __('messages.cart_empty') }}</h3>
        <p class="text-muted mb-4">{{ __('messages.cart_empty_description') }}</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="{{ route('topup') }}" class="btn btn-primary">
                <i class="fas fa-gamepad me-2"></i>{{ __('messages.browse_services') }}
            </a>
            <a href="{{ route('accounts') }}" class="btn btn-outline-primary">
                <i class="fas fa-user me-2"></i>{{ __('messages.browse_accounts') }}
            </a>
        </div>
    </div>

    <!-- Cart Items -->
    <div x-show="!loading && items.length > 0" class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">{{ __('messages.cart_items') }} (<span x-text="items.length"></span>)</h5>
                </div>
                <div class="card-body p-0">
                    <template x-for="item in items" :key="item.id">
                        <div class="border-bottom p-4">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img :src="getItemImage(item)" 
                                         :alt="getItemName(item)" 
                                         class="img-fluid rounded"
                                         style="height: 80px; width: 80px; object-fit: cover;">
                                </div>
                                <div class="col-md-4">
                                    <h6 class="fw-bold mb-1" x-text="getItemName(item)"></h6>
                                    <p class="text-muted small mb-1" x-text="getItemGame(item)"></p>
                                    <span class="badge" 
                                          :class="item.product_type === 'service' ? 'bg-primary' : 'bg-success'"
                                          x-text="item.product_type === 'service' ? '{{ __('messages.service') }}' : '{{ __('messages.account') }}'">
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                @click="updateQuantity(item.id, item.quantity - 1)"
                                                :disabled="item.quantity <= 1 || item.product_type === 'account'">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" 
                                               class="form-control text-center" 
                                               :value="item.quantity"
                                               :disabled="item.product_type === 'account'"
                                               @change="updateQuantity(item.id, $event.target.value)"
                                               min="1" max="10">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                @click="updateQuantity(item.id, item.quantity + 1)"
                                                :disabled="item.quantity >= 10 || item.product_type === 'account'">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="fw-bold text-primary fs-5" x-text="`$${(item.price * item.quantity).toFixed(2)}`"></span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="btn btn-outline-danger btn-sm" 
                                            @click="removeItem(item.id)"
                                            :disabled="removing === item.id">
                                        <i class="fas fa-trash" x-show="removing !== item.id"></i>
                                        <div class="spinner-border spinner-border-sm" x-show="removing === item.id"></div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-outline-danger" @click="clearCart()" :disabled="clearing">
                            <i class="fas fa-trash me-2" x-show="!clearing"></i>
                            <div class="spinner-border spinner-border-sm me-2" x-show="clearing"></div>
                            {{ __('messages.clear_cart') }}
                        </button>
                        <a href="{{ route('topup') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>{{ __('messages.continue_shopping') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">{{ __('messages.order_summary') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('messages.subtotal') }}</span>
                        <span x-text="`$${subtotal.toFixed(2)}`"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" x-show="discount > 0">
                        <span class="text-success">{{ __('messages.discount') }}</span>
                        <span class="text-success" x-text="`-$${discount.toFixed(2)}`"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('messages.tax') }}</span>
                        <span x-text="`$${tax.toFixed(2)}`"></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>{{ __('messages.total') }}</span>
                        <span class="text-primary" x-text="`$${total.toFixed(2)}`"></span>
                    </div>

                    <!-- Coupon Code -->
                    <div class="mt-4">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   placeholder="{{ __('messages.coupon_code') }}"
                                   x-model="couponCode">
                            <button class="btn btn-outline-primary" 
                                    @click="applyCoupon()"
                                    :disabled="applyingCoupon">
                                <span x-show="!applyingCoupon">{{ __('messages.apply') }}</span>
                                <div class="spinner-border spinner-border-sm" x-show="applyingCoupon"></div>
                            </button>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <div class="d-grid mt-4">
                        <button class="btn btn-primary btn-lg" 
                                @click="proceedToCheckout()"
                                :disabled="items.length === 0 || processing">
                            <span x-show="!processing">
                                <i class="fas fa-credit-card me-2"></i>{{ __('messages.proceed_to_checkout') }}
                            </span>
                            <span x-show="processing">
                                <div class="spinner-border spinner-border-sm me-2"></div>{{ __('messages.processing') }}
                            </span>
                        </button>
                    </div>

                    <!-- Security Notice -->
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="fas fa-lock me-1"></i>{{ __('messages.secure_checkout') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function cartManager() {
    return {
        items: [],
        loading: true,
        removing: null,
        clearing: false,
        processing: false,
        applyingCoupon: false,
        couponCode: '',
        appliedCoupon: null,

        get subtotal() {
            return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get discount() {
            return this.appliedCoupon ? this.appliedCoupon.discount_amount : 0;
        },

        get tax() {
            return (this.subtotal - this.discount) * 0.1; // 10% tax
        },

        get total() {
            return this.subtotal - this.discount + this.tax;
        },

        async init() {
            await this.loadCart();
        },

        async loadCart() {
            this.loading = true;
            
            try {
                const response = await fetch('/api/v1/cart');
                const data = await response.json();
                
                if (data.success) {
                    this.items = data.data.items || [];
                    this.appliedCoupon = data.data.coupon || null;
                }
            } catch (error) {
                console.error('Error loading cart:', error);
                showToast('{{ __("messages.error_loading_cart") }}', 'danger');
            } finally {
                this.loading = false;
            }
        },

        async updateQuantity(itemId, quantity) {
            if (quantity < 1 || quantity > 10) return;
            
            try {
                const response = await fetch(`/api/v1/cart/${itemId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ quantity: parseInt(quantity) })
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadCart();
                    updateCartCount();
                } else {
                    showToast(data.message || '{{ __("messages.error_updating_cart") }}', 'danger');
                }
            } catch (error) {
                console.error('Error updating cart:', error);
                showToast('{{ __("messages.error_updating_cart") }}', 'danger');
            }
        },

        async removeItem(itemId) {
            this.removing = itemId;
            
            try {
                const response = await fetch(`/api/v1/cart/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadCart();
                    updateCartCount();
                    showToast('{{ __("messages.item_removed") }}', 'success');
                } else {
                    showToast(data.message || '{{ __("messages.error_removing_item") }}', 'danger');
                }
            } catch (error) {
                console.error('Error removing item:', error);
                showToast('{{ __("messages.error_removing_item") }}', 'danger');
            } finally {
                this.removing = null;
            }
        },

        async clearCart() {
            if (!confirm('{{ __("messages.confirm_clear_cart") }}')) return;
            
            this.clearing = true;
            
            try {
                const response = await fetch('/api/v1/cart/clear', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.items = [];
                    this.appliedCoupon = null;
                    updateCartCount();
                    showToast('{{ __("messages.cart_cleared") }}', 'success');
                } else {
                    showToast(data.message || '{{ __("messages.error_clearing_cart") }}', 'danger');
                }
            } catch (error) {
                console.error('Error clearing cart:', error);
                showToast('{{ __("messages.error_clearing_cart") }}', 'danger');
            } finally {
                this.clearing = false;
            }
        },

        async applyCoupon() {
            if (!this.couponCode.trim()) return;
            
            this.applyingCoupon = true;
            
            try {
                const response = await fetch('/api/v1/cart/coupon', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ code: this.couponCode })
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadCart();
                    this.couponCode = '';
                    showToast('{{ __("messages.coupon_applied") }}', 'success');
                } else {
                    showToast(data.message || '{{ __("messages.invalid_coupon") }}', 'danger');
                }
            } catch (error) {
                console.error('Error applying coupon:', error);
                showToast('{{ __("messages.error_applying_coupon") }}', 'danger');
            } finally {
                this.applyingCoupon = false;
            }
        },

        async proceedToCheckout() {
            if (this.items.length === 0) return;
            
            this.processing = true;
            
            try {
                const response = await fetch('/api/v1/orders/from-cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = `/checkout?order=${data.data.id}`;
                } else {
                    showToast(data.message || '{{ __("messages.error_creating_order") }}', 'danger');
                }
            } catch (error) {
                console.error('Error creating order:', error);
                showToast('{{ __("messages.error_creating_order") }}', 'danger');
            } finally {
                this.processing = false;
            }
        },

        getItemImage(item) {
            if (item.product_type === 'service') {
                return item.service?.image || item.service?.game?.image || '/images/services/default.jpg';
            } else {
                return item.account?.main_image || item.account?.game?.image || '/images/accounts/default.jpg';
            }
        },

        getItemName(item) {
            if (item.product_type === 'service') {
                return item.service?.name || 'Service';
            } else {
                return item.account?.title || 'Account';
            }
        },

        getItemGame(item) {
            if (item.product_type === 'service') {
                return item.service?.game?.name || '';
            } else {
                return item.account?.game?.name || '';
            }
        }
    }
}
</script>
@endpush
