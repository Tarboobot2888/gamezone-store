@extends('layouts.app')

@section('title', __('Frequently Asked Questions') . ' - ' . config('app.name'))
@section('description', __('Find answers to common questions about GameZone services, payments, and account management.'))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="display-5 fw-bold mb-3">{{ __('Frequently Asked Questions') }}</h1>
                <p class="lead text-muted">{{ __('Find answers to common questions about our services') }}</p>
            </div>
        </div>

        <div class="row">
            <!-- FAQ Categories -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Categories') }}</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#general" class="list-group-item list-group-item-action">
                            <i class="fas fa-question-circle me-2"></i>
                            {{ __('General') }}
                        </a>
                        <a href="#payments" class="list-group-item list-group-item-action">
                            <i class="fas fa-credit-card me-2"></i>
                            {{ __('Payments') }}
                        </a>
                        <a href="#topup" class="list-group-item list-group-item-action">
                            <i class="fas fa-bolt me-2"></i>
                            {{ __('Top-Up Services') }}
                        </a>
                        <a href="#accounts" class="list-group-item list-group-item-action">
                            <i class="fas fa-gamepad me-2"></i>
                            {{ __('Game Accounts') }}
                        </a>
                        <a href="#support" class="list-group-item list-group-item-action">
                            <i class="fas fa-headset me-2"></i>
                            {{ __('Support') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- FAQ Content -->
            <div class="col-lg-9">
                <!-- General Questions -->
                <section id="general" class="mb-5">
                    <h3 class="fw-bold mb-4">
                        <i class="fas fa-question-circle text-primary me-2"></i>
                        {{ __('General Questions') }}
                    </h3>
                    <div class="accordion" id="generalAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#general1">
                                    {{ __('What is GameZone?') }}
                                </button>
                            </h2>
                            <div id="general1" class="accordion-collapse collapse show" data-bs-parent="#generalAccordion">
                                <div class="accordion-body">
                                    {{ __('GameZone is a professional gaming marketplace and top-up platform that provides instant top-up services for popular mobile and PC games, as well as a secure marketplace for buying and selling gaming accounts.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#general2">
                                    {{ __('How do I create an account?') }}
                                </button>
                            </h2>
                            <div id="general2" class="accordion-collapse collapse" data-bs-parent="#generalAccordion">
                                <div class="accordion-body">
                                    {{ __('You can create an account by clicking the "Register" button and filling out the registration form. You can also sign up using your Google or GitHub account for faster registration.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#general3">
                                    {{ __('Is GameZone safe and secure?') }}
                                </button>
                            </h2>
                            <div id="general3" class="accordion-collapse collapse" data-bs-parent="#generalAccordion">
                                <div class="accordion-body">
                                    {{ __('Yes, GameZone uses industry-standard security measures including SSL encryption, secure payment processing, and Firebase authentication to protect your personal and financial information.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Payment Questions -->
                <section id="payments" class="mb-5">
                    <h3 class="fw-bold mb-4">
                        <i class="fas fa-credit-card text-success me-2"></i>
                        {{ __('Payment Questions') }}
                    </h3>
                    <div class="accordion" id="paymentsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payment1">
                                    {{ __('What payment methods do you accept?') }}
                                </button>
                            </h2>
                            <div id="payment1" class="accordion-collapse collapse" data-bs-parent="#paymentsAccordion">
                                <div class="accordion-body">
                                    {{ __('We accept major credit and debit cards (Visa, MasterCard, American Express) through Stripe, as well as PayPal payments. All payments are processed securely.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payment2">
                                    {{ __('Are there any additional fees?') }}
                                </button>
                            </h2>
                            <div id="payment2" class="accordion-collapse collapse" data-bs-parent="#paymentsAccordion">
                                <div class="accordion-body">
                                    {{ __('We charge a small processing fee (typically 3%) to cover payment gateway costs. This fee is clearly displayed during checkout before you complete your purchase.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payment3">
                                    {{ __('Can I get a refund?') }}
                                </button>
                            </h2>
                            <div id="payment3" class="accordion-collapse collapse" data-bs-parent="#paymentsAccordion">
                                <div class="accordion-body">
                                    {{ __('Refunds are available for certain circumstances such as failed deliveries or technical issues. Digital products like top-ups are generally non-refundable once delivered. Please contact our support team for assistance.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Top-Up Questions -->
                <section id="topup" class="mb-5">
                    <h3 class="fw-bold mb-4">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        {{ __('Top-Up Services') }}
                    </h3>
                    <div class="accordion" id="topupAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#topup1">
                                    {{ __('How long does it take to receive my top-up?') }}
                                </button>
                            </h2>
                            <div id="topup1" class="accordion-collapse collapse" data-bs-parent="#topupAccordion">
                                <div class="accordion-body">
                                    {{ __('Most top-ups are delivered within 5-10 minutes after payment confirmation. Some services may take up to 24 hours depending on the game and server status.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#topup2">
                                    {{ __('What information do I need to provide?') }}
                                </button>
                            </h2>
                            <div id="topup2" class="accordion-collapse collapse" data-bs-parent="#topupAccordion">
                                <div class="accordion-body">
                                    {{ __('You typically need to provide your Player ID/UID and server information. The exact requirements vary by game and are clearly displayed during the ordering process.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#topup3">
                                    {{ __('What if my top-up fails?') }}
                                </button>
                            </h2>
                            <div id="topup3" class="accordion-collapse collapse" data-bs-parent="#topupAccordion">
                                <div class="accordion-body">
                                    {{ __('If your top-up fails due to incorrect information or technical issues, we will either retry the delivery or provide a full refund. Contact our support team immediately if you experience any issues.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Account Questions -->
                <section id="accounts" class="mb-5">
                    <h3 class="fw-bold mb-4">
                        <i class="fas fa-gamepad text-info me-2"></i>
                        {{ __('Game Accounts') }}
                    </h3>
                    <div class="accordion" id="accountsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#account1">
                                    {{ __('Are the game accounts legitimate?') }}
                                </button>
                            </h2>
                            <div id="account1" class="accordion-collapse collapse" data-bs-parent="#accountsAccordion">
                                <div class="accordion-body">
                                    {{ __('Yes, all game accounts sold on our platform are legitimate and have been verified by our team. We do not sell hacked, stolen, or fraudulent accounts.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#account2">
                                    {{ __('How do I receive my purchased account?') }}
                                </button>
                            </h2>
                            <div id="account2" class="accordion-collapse collapse" data-bs-parent="#accountsAccordion">
                                <div class="accordion-body">
                                    {{ __('After purchase, you will receive the account credentials via email or through your dashboard. We recommend changing the password immediately after receiving the account.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#account3">
                                    {{ __('What if the account gets banned?') }}
                                </button>
                            </h2>
                            <div id="account3" class="accordion-collapse collapse" data-bs-parent="#accountsAccordion">
                                <div class="accordion-body">
                                    {{ __('We provide a limited warranty against bans for accounts that are banned due to previous owner actions within 7 days of purchase. Bans due to your own actions are not covered.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Support Questions -->
                <section id="support" class="mb-5">
                    <h3 class="fw-bold mb-4">
                        <i class="fas fa-headset text-danger me-2"></i>
                        {{ __('Support') }}
                    </h3>
                    <div class="accordion" id="supportAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#support1">
                                    {{ __('How can I contact support?') }}
                                </button>
                            </h2>
                            <div id="support1" class="accordion-collapse collapse" data-bs-parent="#supportAccordion">
                                <div class="accordion-body">
                                    {{ __('You can contact our support team through the contact form, create a support ticket in your dashboard, or email us at support@gamezone.com. We typically respond within 24 hours.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#support2">
                                    {{ __('What are your support hours?') }}
                                </button>
                            </h2>
                            <div id="support2" class="accordion-collapse collapse" data-bs-parent="#supportAccordion">
                                <div class="accordion-body">
                                    {{ __('Our support team is available 24/7 for urgent issues. For general inquiries, we provide support Monday through Friday, 9 AM to 6 PM EST.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#support3">
                                    {{ __('Do you offer live chat support?') }}
                                </button>
                            </h2>
                            <div id="support3" class="accordion-collapse collapse" data-bs-parent="#supportAccordion">
                                <div class="accordion-body">
                                    {{ __('Live chat support is available for premium customers and urgent issues. Regular customers can use our ticket system for comprehensive support.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Still Have Questions -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-primary text-white text-center">
                    <div class="card-body py-5">
                        <h3 class="fw-bold mb-3">{{ __('Still Have Questions?') }}</h3>
                        <p class="lead mb-4">{{ __('Our support team is here to help you with any questions or concerns.') }}</p>
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="{{ route('contact') }}" class="btn btn-light btn-lg">{{ __('Contact Support') }}</a>
                            @auth
                                <a href="{{ route('dashboard.support-tickets') }}" class="btn btn-outline-light btn-lg">{{ __('Create Ticket') }}</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Smooth scrolling for category links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Update active category based on scroll position
window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.list-group-item-action');
    
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 150;
        if (window.pageYOffset >= sectionTop) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + current) {
            link.classList.add('active');
        }
    });
});
</script>
@endpush
@endsection
