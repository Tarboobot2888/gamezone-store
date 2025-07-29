@extends('layouts.app')

@section('title', __('Profile') . ' - ' . config('app.name'))

@section('content')
<div class="pt-5 mt-4">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h2">{{ __('My Profile') }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Profile') }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row" x-data="profileManager()">
            <!-- Profile Picture -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="position-relative d-inline-block mb-3">
                            <img :src="profile.avatar_url" alt="{{ __('Profile Picture') }}" class="rounded-circle border border-3 border-primary" width="150" height="150" style="object-fit: cover;">
                            <button class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle" @click="$refs.avatarInput.click()">
                                <i class="fas fa-camera"></i>
                            </button>
                            <input type="file" x-ref="avatarInput" @change="uploadAvatar($event)" accept="image/*" class="d-none">
                        </div>
                        <h5 class="fw-bold" x-text="profile.name"></h5>
                        <p class="text-muted" x-text="profile.email"></p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-primary" x-text="profile.role"></span>
                            <span class="badge bg-success" x-show="profile.email_verified_at">{{ __('Verified') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Account Stats -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Account Statistics') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="text-primary mb-0" x-text="stats.total_orders"></h4>
                                    <small class="text-muted">{{ __('Total Orders') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success mb-0" x-text="'$' + parseFloat(stats.total_spent).toFixed(2)"></h4>
                                <small class="text-muted">{{ __('Total Spent') }}</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="text-info mb-0" x-text="stats.accounts_purchased"></h4>
                                    <small class="text-muted">{{ __('Accounts') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="text-warning mb-0" x-text="stats.topups_completed"></h4>
                                <small class="text-muted">{{ __('Top-Ups') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Profile Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="updateProfile()">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Full Name') }}</label>
                                    <input type="text" class="form-control" x-model="profile.name" required>
                                    <div class="invalid-feedback" x-show="errors.name" x-text="errors.name"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Email Address') }}</label>
                                    <input type="email" class="form-control" x-model="profile.email" required>
                                    <div class="invalid-feedback" x-show="errors.email" x-text="errors.email"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Phone Number') }}</label>
                                    <input type="tel" class="form-control" x-model="profile.phone">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Date of Birth') }}</label>
                                    <input type="date" class="form-control" x-model="profile.date_of_birth">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Preferred Language') }}</label>
                                    <select class="form-select" x-model="profile.preferred_language">
                                        <option value="en">{{ __('English') }}</option>
                                        <option value="ar">{{ __('العربية') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Theme Preference') }}</label>
                                    <select class="form-select" x-model="profile.theme_preference">
                                        <option value="light">{{ __('Light') }}</option>
                                        <option value="dark">{{ __('Dark') }}</option>
                                        <option value="auto">{{ __('Auto') }}</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Bio') }}</label>
                                    <textarea class="form-control" rows="3" x-model="profile.bio" placeholder="{{ __('Tell us about yourself...') }}"></textarea>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary" :disabled="loading">
                                    <span x-show="!loading">{{ __('Update Profile') }}</span>
                                    <span x-show="loading">
                                        <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Updating...') }}
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Change Password') }}</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="changePassword()">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">{{ __('Current Password') }}</label>
                                    <input type="password" class="form-control" x-model="passwordForm.current_password" required>
                                    <div class="invalid-feedback" x-show="passwordErrors.current_password" x-text="passwordErrors.current_password"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('New Password') }}</label>
                                    <input type="password" class="form-control" x-model="passwordForm.password" required>
                                    <div class="invalid-feedback" x-show="passwordErrors.password" x-text="passwordErrors.password"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Confirm New Password') }}</label>
                                    <input type="password" class="form-control" x-model="passwordForm.password_confirmation" required>
                                    <div class="invalid-feedback" x-show="passwordErrors.password_confirmation" x-text="passwordErrors.password_confirmation"></div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-warning" :disabled="passwordLoading">
                                    <span x-show="!passwordLoading">{{ __('Change Password') }}</span>
                                    <span x-show="passwordLoading">
                                        <span class="spinner-border spinner-border-sm me-2"></span>{{ __('Changing...') }}
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Account Settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="profile.email_notifications" @change="updateNotificationSettings()">
                                    <label class="form-check-label">
                                        {{ __('Email Notifications') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="profile.marketing_emails" @change="updateNotificationSettings()">
                                    <label class="form-check-label">
                                        {{ __('Marketing Emails') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="profile.two_factor_enabled" @change="toggleTwoFactor()">
                                    <label class="form-check-label">
                                        {{ __('Two-Factor Authentication') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-danger" @click="deleteAccount()">
                                {{ __('Delete Account') }}
                            </button>
                            <button class="btn btn-outline-secondary" @click="downloadData()">
                                {{ __('Download My Data') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function profileManager() {
    return {
        loading: false,
        passwordLoading: false,
        errors: {},
        passwordErrors: {},
        profile: @json(auth()->user()),
        stats: {},
        passwordForm: {
            current_password: '',
            password: '',
            password_confirmation: ''
        },

        init() {
            this.loadStats();
        },

        async loadStats() {
            try {
                const response = await fetch('/api/user/stats');
                const data = await response.json();
                this.stats = data;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async updateProfile() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch('/api/user/profile', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.profile)
                });

                const data = await response.json();

                if (data.success) {
                    showToast('{{ __("Profile updated successfully") }}', 'success');
                    this.profile = data.user;
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        showToast(data.message || '{{ __("Error updating profile") }}', 'danger');
                    }
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                showToast('{{ __("Error updating profile") }}', 'danger');
            } finally {
                this.loading = false;
            }
        },

        async changePassword() {
            this.passwordLoading = true;
            this.passwordErrors = {};

            try {
                const response = await fetch('/api/user/password', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.passwordForm)
                });

                const data = await response.json();

                if (data.success) {
                    showToast('{{ __("Password changed successfully") }}', 'success');
                    this.passwordForm = {
                        current_password: '',
                        password: '',
                        password_confirmation: ''
                    };
                } else {
                    if (data.errors) {
                        this.passwordErrors = data.errors;
                    } else {
                        showToast(data.message || '{{ __("Error changing password") }}', 'danger');
                    }
                }
            } catch (error) {
                console.error('Error changing password:', error);
                showToast('{{ __("Error changing password") }}', 'danger');
            } finally {
                this.passwordLoading = false;
            }
        },

        async uploadAvatar(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('avatar', file);

            try {
                const response = await fetch('/api/user/avatar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.profile.avatar_url = data.avatar_url;
                    showToast('{{ __("Avatar updated successfully") }}', 'success');
                } else {
                    showToast(data.message || '{{ __("Error uploading avatar") }}', 'danger');
                }
            } catch (error) {
                console.error('Error uploading avatar:', error);
                showToast('{{ __("Error uploading avatar") }}', 'danger');
            }
        },

        async updateNotificationSettings() {
            try {
                const response = await fetch('/api/user/notifications', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        email_notifications: this.profile.email_notifications,
                        marketing_emails: this.profile.marketing_emails
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showToast('{{ __("Notification settings updated") }}', 'success');
                } else {
                    showToast(data.message || '{{ __("Error updating settings") }}', 'danger');
                }
            } catch (error) {
                console.error('Error updating notification settings:', error);
                showToast('{{ __("Error updating settings") }}', 'danger');
            }
        },

        async toggleTwoFactor() {
            // Two-factor authentication toggle logic
            showToast('{{ __("Two-factor authentication feature coming soon") }}', 'info');
        },

        async deleteAccount() {
            if (confirm('{{ __("Are you sure you want to delete your account? This action cannot be undone.") }}')) {
                // Account deletion logic
                showToast('{{ __("Account deletion feature coming soon") }}', 'info');
            }
        },

        async downloadData() {
            // Data download logic
            showToast('{{ __("Data download feature coming soon") }}', 'info');
        }
    }
}
</script>
@endpush
@endsection
