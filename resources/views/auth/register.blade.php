@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">{{ __('messages.create_account') }}</h2>
                        <p class="text-muted">{{ __('messages.register_subtitle') }}</p>
                    </div>

                    <!-- Social Registration -->
                    <div class="d-grid gap-2 mb-4">
                        <button type="button" class="btn btn-outline-danger" onclick="signUpWithGoogle()">
                            <i class="fab fa-google me-2"></i>{{ __('messages.continue_with_google') }}
                        </button>
                        <button type="button" class="btn btn-outline-dark" onclick="signUpWithGitHub()">
                            <i class="fab fa-github me-2"></i>{{ __('messages.continue_with_github') }}
                        </button>
                    </div>

                    <div class="text-center mb-4">
                        <span class="text-muted">{{ __('messages.or') }}</span>
                    </div>

                    <!-- Registration Form -->
                    <form method="POST" action="{{ route('register') }}" x-data="registerForm()">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('messages.full_name') }}</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus
                                   x-model="form.name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('messages.email') }}</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required
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

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">{{ __('messages.confirm_password') }}</label>
                            <div class="input-group">
                                <input :type="showConfirmPassword ? 'text' : 'password'" 
                                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required
                                       x-model="form.password_confirmation">
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        @click="showConfirmPassword = !showConfirmPassword">
                                    <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                {{ __('messages.agree_to') }} 
                                <a href="#" class="text-decoration-none">{{ __('messages.terms_of_service') }}</a> 
                                {{ __('messages.and') }} 
                                <a href="#" class="text-decoration-none">{{ __('messages.privacy_policy') }}</a>
                            </label>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" 
                                    class="btn btn-primary btn-lg" 
                                    :disabled="loading"
                                    x-text="loading ? '{{ __('messages.creating_account') }}...' : '{{ __('messages.create_account') }}'">
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <span class="text-muted">{{ __('messages.already_have_account') }}</span>
                        <a href="{{ route('login') }}" class="text-decoration-none fw-bold">
                            {{ __('messages.sign_in') }}
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
function registerForm() {
    return {
        form: {
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        },
        showPassword: false,
        showConfirmPassword: false,
        loading: false,

        async submitForm() {
            this.loading = true;
            // Form will be submitted normally
        }
    }
}

async function signUpWithGoogle() {
    try {
        showLoading();
        const user = await window.FirebaseAuth.signInWithGoogle();
        showToast('{{ __("messages.registration_successful") }}', 'success');
        
        // Redirect will be handled by Firebase auth state change
    } catch (error) {
        hideLoading();
        console.error('Google sign-up error:', error);
        showToast(error.message || '{{ __("messages.registration_failed") }}', 'danger');
    }
}

async function signUpWithGitHub() {
    try {
        showLoading();
        const user = await window.FirebaseAuth.signInWithGitHub();
        showToast('{{ __("messages.registration_successful") }}', 'success');
        
        // Redirect will be handled by Firebase auth state change
    } catch (error) {
        hideLoading();
        console.error('GitHub sign-up error:', error);
        showToast(error.message || '{{ __("messages.registration_failed") }}', 'danger');
    }
}
</script>
@endpush
