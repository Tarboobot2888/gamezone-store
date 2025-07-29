@extends('layouts.app')

@section('title', __('Verify Email') . ' - ' . config('app.name'))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4 text-center">
                        <!-- Email Icon -->
                        <div class="mb-4">
                            <svg width="80" height="80" viewBox="0 0 80 80" class="text-primary">
                                <circle cx="40" cy="40" r="35" fill="currentColor" opacity="0.1"/>
                                <rect x="20" y="30" width="40" height="25" rx="3" fill="none" stroke="currentColor" stroke-width="2"/>
                                <path d="M20 35l20 12 20-12" stroke="currentColor" stroke-width="2" fill="none"/>
                                <circle cx="60" cy="25" r="8" fill="#10b981"/>
                                <path d="M57 25l2 2 4-4" stroke="white" stroke-width="1.5" fill="none"/>
                            </svg>
                        </div>

                        <h2 class="fw-bold mb-3">{{ __('Verify Your Email') }}</h2>
                        <p class="text-muted mb-4">
                            {{ __('We\'ve sent a verification link to your email address. Please check your inbox and click the link to verify your account.') }}
                        </p>

                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __('Didn\'t receive the email? Check your spam folder or request a new one.') }}
                            </small>
                        </div>

                        <!-- Resend Verification -->
                        <form method="POST" action="{{ route('verification.send') }}" x-data="{ loading: false }">
                            @csrf
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary" :disabled="loading" @click="loading = true">
                                    <span x-show="!loading">{{ __('Resend Verification Email') }}</span>
                                    <span x-show="loading">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        {{ __('Sending...') }}
                                    </span>
                                </button>
                            </div>
                        </form>

                        <!-- Back to Login -->
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ __('Back to Login') }}
                            </a>
                        </div>

                        <!-- Logout -->
                        <div class="mt-3">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-muted p-0">
                                    {{ __('Sign out and use a different account') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (session('status') == 'verification-link-sent')
    @push('scripts')
    <script>
        showToast('{{ __("A new verification link has been sent to your email address.") }}', 'success');
    </script>
    @endpush
@endif
@endsection
