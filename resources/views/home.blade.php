@extends('layouts.app')

@section('title', __('messages.home') . ' - ' . config('app.name'))
@section('description', __('messages.home_description'))

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-dark text-white py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold gaming-font mb-4">
                    {{ __('messages.hero_title') }}
                </h1>
                <p class="lead mb-4">
                    {{ __('messages.hero_subtitle') }}
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('topup') }}" class="btn btn-light btn-lg me-3 mb-2">
                        <i class="fas fa-gamepad me-2"></i>
                        {{ __('messages.start_shopping') }}
                    </a>
                    <a href="{{ route('accounts') }}" class="btn btn-outline-light btn-lg mb-2">
                        <i class="fas fa-user-circle me-2"></i>
                        {{ __('messages.browse_accounts') }}
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="{{ asset('images/hero-gaming.png') }}" alt="Gaming Hero" class="img-fluid hero-image">
            </div>
        </div>
    </div>
</section>

<!-- Featured Games -->
<section class="featured-games py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="gaming-font fw-bold">{{ __('messages.featured_games') }}</h2>
                <p class="text-muted">{{ __('messages.featured_games_subtitle') }}</p>
            </div>
        </div>
        <div class="row" id="featured-games-container">
            <!-- Games will be loaded via JavaScript -->
        </div>
    </div>
</section>

<!-- Popular Services -->
<section class="popular-services py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="gaming-font fw-bold">{{ __('messages.popular_services') }}</h2>
                <p class="text-muted">{{ __('messages.popular_services_subtitle') }}</p>
            </div>
        </div>
        <div class="row" id="popular-services-container">
            <!-- Services will be loaded via JavaScript -->
        </div>
    </div>
</section>

<!-- Featured Accounts -->
<section class="featured-accounts py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="gaming-font fw-bold">{{ __('messages.featured_accounts') }}</h2>
                <p class="text-muted">{{ __('messages.featured_accounts_subtitle') }}</p>
            </div>
        </div>
        <div class="row" id="featured-accounts-container">
            <!-- Accounts will be loaded via JavaScript -->
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="why-choose-us py-5 bg-dark text-white">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="gaming-font fw-bold">{{ __('messages.why_choose_us') }}</h2>
                <p class="text-muted">{{ __('messages.why_choose_us_subtitle') }}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold">{{ __('messages.secure_transactions') }}</h5>
                    <p class="text-muted">{{ __('messages.secure_transactions_desc') }}</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-bolt fa-3x text-warning"></i>
                    </div>
                    <h5 class="fw-bold">{{ __('messages.instant_delivery') }}</h5>
                    <p class="text-muted">{{ __('messages.instant_delivery_desc') }}</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-headset fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold">{{ __('messages.24_7_support') }}</h5>
                    <p class="text-muted">{{ __('messages.24_7_support_desc') }}</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-tags fa-3x text-info"></i>
                    </div>
                    <h5 class="fw-bold">{{ __('messages.best_prices') }}</h5>
                    <p class="text-muted">{{ __('messages.best_prices_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center text-white">
                <h3 class="gaming-font fw-bold mb-3">{{ __('messages.stay_updated') }}</h3>
                <p class="mb-4">{{ __('messages.newsletter_signup_desc') }}</p>
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form" x-data="{ email: '', loading: false }">
                    @csrf
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="email" name="email" x-model="email" class="form-control form-control-lg" 
                                       placeholder="{{ __('messages.enter_email') }}" required>
                                <button class="btn btn-light btn-lg" type="submit" :disabled="loading">
                                    <span x-show="!loading">{{ __('messages.subscribe') }}</span>
                                    <span x-show="loading">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.hero-section {
    min-height: 60vh;
}

.hero-image {
    max-height: 400px;
    filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
}

.feature-card {
    transition: transform 0.3s ease;
    border-radius: 10px;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.game-card, .service-card, .account-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
}

.game-card:hover, .service-card:hover, .account-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.gaming-font {
    font-family: 'Orbitron', system-ui, sans-serif;
}

.min-vh-50 {
    min-height: 50vh;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load featured games
    loadFeaturedGames();
    
    // Load popular services
    loadPopularServices();
    
    // Load featured accounts
    loadFeaturedAccounts();
    
    // Newsletter form submission
    setupNewsletterForm();
});

async function loadFeaturedGames() {
    try {
        const response = await fetch('/api/v1/games/featured');
        const data = await response.json();
        
        if (data.success) {
            renderGames(data.data);
        }
    } catch (error) {
        console.error('Error loading featured games:', error);
    }
}

async function loadPopularServices() {
    try {
        const response = await fetch('/api/v1/services/featured');
        const data = await response.json();
        
        if (data.success) {
            renderServices(data.data);
        }
    } catch (error) {
        console.error('Error loading popular services:', error);
    }
}

async function loadFeaturedAccounts() {
    try {
        const response = await fetch('/api/v1/accounts/featured');
        const data = await response.json();
        
        if (data.success) {
            renderAccounts(data.data);
        }
    } catch (error) {
        console.error('Error loading featured accounts:', error);
    }
}

function renderGames(games) {
    const container = document.getElementById('featured-games-container');
    container.innerHTML = games.map(game => `
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card game-card h-100 border-0 shadow">
                <img src="${game.image_url || '/images/games/default.jpg'}" class="card-img-top" alt="${game.name}">
                <div class="card-body">
                    <h5 class="card-title fw-bold">${game.name}</h5>
                    <p class="card-text text-muted small">${game.description || ''}</p>
                    <a href="/topup?game=${game.slug}" class="btn btn-gaming btn-sm">
                        <i class="fas fa-gamepad me-1"></i>
                        {{ __('messages.top_up_now') }}
                    </a>
                </div>
            </div>
        </div>
    `).join('');
}

function renderServices(services) {
    const container = document.getElementById('popular-services-container');
    container.innerHTML = services.map(service => `
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card service-card h-100 border-0 shadow">
                <img src="${service.image_url || '/images/services/default.jpg'}" class="card-img-top" alt="${service.name}">
                <div class="card-body">
                    <h5 class="card-title fw-bold">${service.name}</h5>
                    <p class="card-text text-muted small">${service.description || ''}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h6 text-primary mb-0">$${service.price}</span>
                        <button class="btn btn-gaming btn-sm" onclick="addToCart('service', ${service.id})">
                            <i class="fas fa-cart-plus me-1"></i>
                            {{ __('messages.add_to_cart') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function renderAccounts(accounts) {
    const container = document.getElementById('featured-accounts-container');
    container.innerHTML = accounts.map(account => `
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card account-card h-100 border-0 shadow">
                <img src="${account.image_url || '/images/accounts/default.jpg'}" class="card-img-top" alt="${account.title}">
                <div class="card-body">
                    <h5 class="card-title fw-bold">${account.title}</h5>
                    <p class="card-text text-muted small">${account.description || ''}</p>
                    <div class="mb-2">
                        <small class="text-muted">{{ __('messages.level') }}: ${account.level || 'N/A'}</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h6 text-primary mb-0">$${account.price}</span>
                        <a href="/account/${account.id}" class="btn btn-gaming btn-sm">
                            <i class="fas fa-eye me-1"></i>
                            {{ __('messages.view_details') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function setupNewsletterForm() {
    const form = document.querySelector('.newsletter-form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('{{ __('messages.newsletter_subscribed') }}', 'success');
                    form.reset();
                } else {
                    showToast(data.message || '{{ __('messages.error_occurred') }}', 'error');
                }
            } catch (error) {
                showToast('{{ __('messages.error_occurred') }}', 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
}
</script>
@endpush
