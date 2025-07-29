@extends('layouts.app')

@section('title', __('Reset Password') . ' - ' . config('app.name'))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <!-- Key Icon -->
                        <div class="text-center mb-4">
                            <svg width="64" height="64" viewBox="0 0 64 64" class="text-success">
                                <circle cx="32" cy="32" r="28" fill="currentColor" opacity="0.1"/>
                                <path d="M20 32a12 12 0 1 1 24 0 12 12 0 0 1-24 0z" fill="none" stroke="currentColor" stroke-width="2"/>
                                <path d="M32 26v12M26 32h12" stroke="currentColor" stroke-width="2"/>
                                <path d="M38 32h6l2-2v-4l-2-2h-6" fill="none" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <h2 class="fw-bold mt-3">{{ __('Set New Password') }}</h2>
                            <p class="text-muted">{{ __('Please enter your new password below.') }}</p>
                        </div>

                        <!-- Reset Form -->
                        <form method="POST" action="{{ route('password.update') }}" x-data="passwordReset()">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" readonly>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('New Password') }}</label>
                                <div class="input-group">
                                    <input id="password" :type="showPassword ? 'text' : 'password'" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           name="password" required autocomplete="new-password" x-model="password">
                                    <button type="button" class="btn btn-outline-secondary" @click="showPassword = !showPassword">
                                        <span x-show="!showPassword">👁️</span>
                                        <span x-show="showPassword">🙈</span>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <!-- Password Strength Indicator -->
                                <div class="mt-2">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar" :class="passwordStrengthClass" 
                                             :style="`width: ${passwordStrength}%`"></div>
                                    </div>
                                    <small class="text-muted" x-text="passwordStrengthText"></small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                                <input id="password-confirm" type="password" class="form-control" 
                                       name="password_confirmation" required autocomplete="new-password" x-model="passwordConfirm">
                                <div x-show="passwordConfirm && password !== passwordConfirm" class="text-danger mt-1">
                                    <small>{{ __('Passwords do not match') }}</small>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-success" :disabled="loading || password !== passwordConfirm" @click="loading = true">
                                    <span x-show="!loading">{{ __('Reset Password') }}</span>
                                    <span x-show="loading">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        {{ __('Resetting...') }}
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

@push('scripts')
<script>
function passwordReset() {
    return {
        password: '',
        passwordConfirm: '',
        showPassword: false,
        loading: false,
        
        get passwordStrength() {
            if (!this.password) return 0;
            
            let strength = 0;
            if (this.password.length >= 8) strength += 25;
            if (/[a-z]/.test(this.password)) strength += 25;
            if (/[A-Z]/.test(this.password)) strength += 25;
            if (/[0-9]/.test(this.password)) strength += 25;
            
            return strength;
        },
        
        get passwordStrengthClass() {
            if (this.passwordStrength < 50) return 'bg-danger';
            if (this.passwordStrength < 75) return 'bg-warning';
            return 'bg-success';
        },
        
        get passwordStrengthText() {
            if (!this.password) return '';
            if (this.passwordStrength < 50) return '{{ __("Weak password") }}';
            if (this.passwordStrength < 75) return '{{ __("Medium password") }}';
            return '{{ __("Strong password") }}';
        }
    }
}
</script>
@endpush
@endsection
