@extends('layouts.app')

@section('title', __('Contact Us') . ' - ' . config('app.name'))
@section('description', __('Get in touch with GameZone support team. We are here to help with your gaming needs.'))

@section('content')
<div class="pt-5 mt-4">
    <!-- Page Header -->
    <section class="bg-primary text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-5 fw-bold mb-3">{{ __('Contact Us') }}</h1>
                    <p class="lead">{{ __('We are here to help! Get in touch with our support team.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form and Info -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title mb-0">{{ __('Send us a Message') }}</h3>
                        </div>
                        <div class="card-body">
                            <form x-data="contactForm()" @submit.prevent="submitForm()">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" x-model="form.name" required>
                                        <div class="invalid-feedback" x-show="errors.name" x-text="errors.name"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" x-model="form.email" required>
                                        <div class="invalid-feedback" x-show="errors.email" x-text="errors.email"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Phone Number') }}</label>
                                        <input type="tel" class="form-control" x-model="form.phone">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Subject') }} <span class="text-danger">*</span></label>
                                        <select class="form-select" x-model="form.subject" required>
                                            <option value="">{{ __('Select Subject') }}</option>
                                            <option value="general">{{ __('General Inquiry') }}</option>
                                            <option value="support">{{ __('Technical Support') }}</option>
                                            <option value="billing">{{ __('Billing Issue') }}</option>
                                            <option value="account">{{ __('Account Problem') }}</option>
                                            <option value="refund">{{ __('Refund Request') }}</option>
                                            <option value="partnership">{{ __('Partnership') }}</option>
                                        </select>
                                        <div class="invalid-feedback" x-show="errors.subject" x-text="errors.subject"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ __('Order Number') }}</label>
                                        <input type="text" class="form-control" x-model="form.order_number" placeholder="{{ __('If related to an order') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ __('Message') }} <span class="text-danger">*</span></label>
                                        <textarea class="form-control" rows="6" x-model="form.message" required placeholder="{{ __('Please describe your inquiry in detail...') }}"></textarea>
                                        <div class="invalid-feedback" x-show="errors.message" x-text="errors.message"></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" x-model="form.newsletter" id="newsletter">
                                            <label class="form-check-label" for="newsletter">
                                                {{ __('Subscribe to our newsletter for updates and promotions') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
                                        <span x-show="!loading">
                                            <i class="fas fa-paper-plane me-2"></i>{{ __('Send Message') }}
                                        </span>
                                        <span x-show="loading">
                                            <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Sending...') }}
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="col-lg-4">
                    <!-- Contact Details -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ __('Contact Information') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="contact-item mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="fas fa-envelope fa-lg text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ __('Email') }}</h6>
                                        <p class="mb-0 text-muted">support@gamezone.com</p>
                                    </div>
                                </div>
                            </div>

                            <div class="contact-item mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="fas fa-phone fa-lg text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ __('Phone') }}</h6>
                                        <p class="mb-0 text-muted">+1 (555) 123-4567</p>
                                    </div>
                                </div>
                            </div>

                            <div class="contact-item mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="fas fa-clock fa-lg text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ __('Support Hours') }}</h6>
                                        <p class="mb-0 text-muted">24/7 Online Support</p>
                                    </div>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="fas fa-headset fa-lg text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ __('Live Chat') }}</h6>
                                        <p class="mb-0 text-muted">{{ __('Available for premium users') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Link -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <i class="fas fa-question-circle fa-3x text-primary mb-3"></i>
                            <h5 class="fw-bold">{{ __('Frequently Asked Questions') }}</h5>
                            <p class="text-muted">{{ __('Find quick answers to common questions') }}</p>
                            <a href="{{ route('faq') }}" class="btn btn-outline-primary">
                                {{ __('View FAQ') }}
                            </a>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ __('Follow Us') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-3">
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="btn btn-outline-info btn-sm">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="btn btn-outline-danger btn-sm">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-discord"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Response Time Notice -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="alert alert-info d-inline-block">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('Response Time:') }}</strong>
                        {{ __('We typically respond to all inquiries within 24 hours. For urgent matters, please use live chat or call us directly.') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
function contactForm() {
    return {
        loading: false,
        errors: {},
        form: {
            name: '{{ auth()->user()->name ?? "" }}',
            email: '{{ auth()->user()->email ?? "" }}',
            phone: '',
            subject: '',
            order_number: '',
            message: '',
            newsletter: false
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch('/api/contact', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    showToast('{{ __("Message sent successfully! We will get back to you soon.") }}', 'success');
                    this.resetForm();
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        showToast(data.message || '{{ __("Error sending message") }}', 'danger');
                    }
                }
            } catch (error) {
                console.error('Error sending message:', error);
                showToast('{{ __("Error sending message") }}', 'danger');
            } finally {
                this.loading = false;
            }
        },

        resetForm() {
            this.form = {
                name: '{{ auth()->user()->name ?? "" }}',
                email: '{{ auth()->user()->email ?? "" }}',
                phone: '',
                subject: '',
                order_number: '',
                message: '',
                newsletter: false
            };
        }
    }
}
</script>
@endpush
@endsection
