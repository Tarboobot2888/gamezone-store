@extends('layouts.app')

@section('title', __('messages.login') . ' - ' . config('app.name'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">{{ __('messages.welcome_back') }}</h2>
                        <p class="text-muted">{{ __('messages.login_subtitle') }}</p>
                    </div>

                    <!-- Social Login -->
                    <div class="d-grid gap-2 mb-4">
                        <button type="button" class="btn btn-outline-danger" onclick="signInWithGoogle()">
                            <i class="fab fa-google me-2"></i>{{ __('messages.continue_with_google') }}
                        </button>
                        <button type="button" class="btn btn-outline-dark" onclick="signInWithGitHub()">
                            <i class="fab fa-github me-2"></i>{{ __('messages.continue_with_github') }}
                        </button>
                    </div>

                    <div class="text-center mb-4">
                        <span class="text-muted">{{ __('messages.or') }}</span>
                    </div>

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" x-data="loginForm()">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('messages.email') }}</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus
                                   x-model="form.email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('messages.password') }}</label>
                            <div class="input-group">
                                <input :type="showPassword ? 'text' : 'password'" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required
                                       x-model="form.password">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        @click="showPassword = !showPassword">
                                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('messages.remember_me') }}
                            </label>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" 
                                    class="btn btn-primary btn-lg" 
                                    :disabled="loading"
                                    x-text="loading ? '{{ __('messages.signing_in') }}...' : '{{ __('messages.sign_in') }}'">
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('password.request') }}" class="text-decoration-none">
                                {{ __('messages.forgot_password') }}
                            </a>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <span class="text-muted">{{ __('messages.dont_have_account') }}</span>
                        <a href="{{ route('register') }}" class="text-decoration-none fw-bold">
                            {{ __('messages.sign_up') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loginForm() {
    return {
        form: {
            email: '',
            password: ''
        },
        showPassword: false,
        loading: false,

        async submitForm() {
            this.loading = true;
            // Form will be submitted normally
        }
    }
}

async function signInWithGoogle() {
    try {
        showLoading();
        const user = await window.FirebaseAuth.signInWithGoogle();
        showToast('{{ __("messages.login_successful") }}', 'success');
        
        // Redirect will be handled by Firebase auth state change
    } catch (error) {
        hideLoading();
        console.error('Google sign-in error:', error);
        showToast(error.message || '{{ __("messages.login_failed") }}', 'danger');
    }
}

async function signInWithGitHub() {
    try {
        showLoading();
        const user = await window.FirebaseAuth.signInWithGitHub();
        showToast('{{ __("messages.login_successful") }}', 'success');
        
        // Redirect will be handled by Firebase auth state change
    } catch (error) {
        hideLoading();
        console.error('GitHub sign-in error:', error);
        showToast(error.message || '{{ __("messages.login_failed") }}', 'danger');
    }
}
</script>
@endpush
