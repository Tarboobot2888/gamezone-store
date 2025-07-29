<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <!-- Brand & Description -->
            <div class="col-lg-4 mb-4">
                <h5 class="gaming-font fw-bold mb-3">
                    <span class="gaming-gradient">GameZone</span>
                </h5>
                <p class="text-muted">
                    {{ __('messages.footer_description') }}
                </p>
                <div class="social-links">
                    <a href="#" class="text-light me-3" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-light me-3" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-light me-3" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-light me-3" title="Discord">
                        <i class="fab fa-discord"></i>
                    </a>
                    <a href="#" class="text-light" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">{{ __('messages.quick_links') }}</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('home') }}" class="text-muted text-decoration-none">
                            {{ __('messages.home') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('topup') }}" class="text-muted text-decoration-none">
                            {{ __('messages.topup') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('accounts') }}" class="text-muted text-decoration-none">
                            {{ __('messages.accounts') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('faq') }}" class="text-muted text-decoration-none">
                            {{ __('messages.faq') }}
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Support -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">{{ __('messages.support') }}</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('contact') }}" class="text-muted text-decoration-none">
                            {{ __('messages.contact_us') }}
                        </a>
                    </li>
                    @auth
                        <li class="mb-2">
                            <a href="{{ route('dashboard.support-tickets.index') }}" class="text-muted text-decoration-none">
                                {{ __('messages.support_tickets') }}
                            </a>
                        </li>
                    @endauth
                    <li class="mb-2">
                        <a href="#" class="text-muted text-decoration-none">
                            {{ __('messages.terms_of_service') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-muted text-decoration-none">
                            {{ __('messages.privacy_policy') }}
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Account -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">{{ __('messages.account') }}</h6>
                <ul class="list-unstyled">
                    @auth
                        <li class="mb-2">
                            <a href="{{ route('dashboard.index') }}" class="text-muted text-decoration-none">
                                {{ __('messages.dashboard') }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('dashboard.orders') }}" class="text-muted text-decoration-none">
                                {{ __('messages.my_orders') }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('dashboard.profile') }}" class="text-muted text-decoration-none">
                                {{ __('messages.profile') }}
                            </a>
                        </li>
                    @else
                        <li class="mb-2">
                            <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                                {{ __('messages.login') }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('register') }}" class="text-muted text-decoration-none">
                                {{ __('messages.register') }}
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">{{ __('messages.newsletter') }}</h6>
                <p class="text-muted small">{{ __('messages.newsletter_description') }}</p>
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                    @csrf
                    <div class="input-group input-group-sm">
                        <input type="email" name="email" class="form-control" 
                               placeholder="{{ __('messages.enter_email') }}" required>
                        <button class="btn btn-gaming" type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <hr class="my-4">

        <!-- Bottom Footer -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted small mb-0">
                    &copy; {{ date('Y') }} GameZone. {{ __('messages.all_rights_reserved') }}
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="payment-methods">
                    <img src="{{ asset('images/payments/visa.png') }}" alt="Visa" class="payment-icon">
                    <img src="{{ asset('images/payments/mastercard.png') }}" alt="Mastercard" class="payment-icon">
                    <img src="{{ asset('images/payments/paypal.png') }}" alt="PayPal" class="payment-icon">
                    <img src="{{ asset('images/payments/stripe.png') }}" alt="Stripe" class="payment-icon">
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.gaming-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.social-links a {
    transition: color 0.3s ease;
}

.social-links a:hover {
    color: #667eea !important;
}

.payment-icon {
    height: 24px;
    margin-left: 8px;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.payment-icon:hover {
    opacity: 1;
}

.newsletter-form .btn-gaming {
    background: linear-gradient(45deg, #667eea, #764ba2);
    border: none;
}

.newsletter-form .btn-gaming:hover {
    background: linear-gradient(45deg, #764ba2, #667eea);
}
</style>
