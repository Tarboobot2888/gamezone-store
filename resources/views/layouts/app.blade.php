<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ session('theme', 'light') === 'dark' ? '#0f172a' : '#ffffff' }}">

    <title>{{ $title ?? config('app.name', 'GameZone') }}</title>
    <meta name="description" content="@yield('description', 'Professional multi-language gaming marketplace for top-ups, accounts, and gaming services.')">
    <meta name="keywords" content="gaming, marketplace, top-up, PUBG Mobile, Free Fire, Mobile Legends, game accounts">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Styles -->
    @if(app()->getLocale() === 'ar')
        @vite(['resources/sass/app-rtl.scss'])
    @else
        @vite(['resources/sass/app.scss'])
    @endif
    
    @if(session('theme', 'light') === 'dark')
        @vite(['resources/sass/dark.scss'])
    @else
        @vite(['resources/sass/light.scss'])
    @endif

    @stack('styles')
</head>
<body class="@yield('body-class', '')" x-data="{ 
    theme: localStorage.getItem('theme') || 'light',
    language: '{{ app()->getLocale() }}',
    cartCount: {{ auth()->check() ? auth()->user()->cartItems()->count() : 0 }}
}" x-init="
    $watch('theme', value => {
        localStorage.setItem('theme', value);
        document.documentElement.setAttribute('data-theme', value);
    })
">
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-75" style="z-index: 9999; display: none !important;">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Please wait...</p>
        </div>
    </div>

    <!-- Navigation -->
    @include('partials.navbar')

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('partials.footer')

    <!-- Mobile Navigation -->
    <div class="mobile-nav d-md-none">
        <div class="container-fluid">
            <div class="row text-center">
                <div class="col">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <div class="nav-icon">🏠</div>
                        <div>{{ __('messages.home') }}</div>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('topup') }}" class="nav-link {{ request()->routeIs('topup') ? 'active' : '' }}">
                        <div class="nav-icon">🎮</div>
                        <div>{{ __('messages.topup') }}</div>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('accounts') }}" class="nav-link {{ request()->routeIs('accounts') ? 'active' : '' }}">
                        <div class="nav-icon">👤</div>
                        <div>{{ __('messages.accounts') }}</div>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('cart') }}" class="nav-link {{ request()->routeIs('cart') ? 'active' : '' }}">
                        <div class="nav-icon position-relative">
                            🛒
                            <span id="mobile-cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count" style="display: none;">
                                0
                            </span>
                        </div>
                        <div>{{ __('messages.cart') }}</div>
                    </a>
                </div>
                <div class="col">
                    @auth
                        <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                            <div class="nav-icon">📊</div>
                            <div>{{ __('messages.dashboard') }}</div>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">
                            <div class="nav-icon">🔐</div>
                            <div>{{ __('messages.login') }}</div>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @if(request()->routeIs('admin.*'))
        @vite(['resources/js/admin.js'])
    @endif

    @stack('scripts')

    <!-- Global JavaScript Variables -->
    <script>
        window.App = {
            csrfToken: '{{ csrf_token() }}',
            locale: '{{ app()->getLocale() }}',
            user: @json(auth()->user()),
            routes: {
                home: '{{ route('home') }}',
                login: '{{ route('login') }}',
                register: '{{ route('register') }}',
                cart: '{{ route('cart') }}',
                dashboard: '{{ auth()->check() ? route('dashboard.index') : '' }}',
            }
        };

        // Initialize cart count update for authenticated users
        @auth
            updateCartCount();
        @endauth
    </script>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('{{ session('success') }}', 'success');
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('{{ session('error') }}', 'danger');
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @foreach($errors->all() as $error)
                    showToast('{{ $error }}', 'danger');
                @endforeach
            });
        </script>
    @endif

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
