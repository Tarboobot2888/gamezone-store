@extends('layouts.app')

@section('title', __('Reset Password') . ' - ' . config('app.name'))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <!-- Lock Icon -->
                        <div class="text-center mb-4">
                            <svg width="64" height="64" viewBox="0 0 64 64" class="text-primary">
                                <circle cx="32" cy="32" r="28" fill="currentColor" opacity="0.1"/>
                                <rect x="20" y="28" width="24" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
                                <path d="M24 28V24a8 8 0 0 1 16 0v4" fill="none" stroke="currentColor" stroke-width="2"/>
                                <circle cx="32" cy="36" r="2" fill="currentColor"/>
                            </svg>
                            <h2 class="fw-bold mt-3">{{ __('Reset Password') }}</h2>
                            <p class="text-muted">{{ __('Enter your email address and we\'ll send you a link to reset your password.') }}</p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <!-- Reset Form -->
                        <form method="POST" action="{{ route('password.email') }}" x-data="{ loading: false }">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary" :disabled="loading" @click="loading = true">
                                    <span x-show="!loading">{{ __('Send Password Reset Link') }}</span>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
