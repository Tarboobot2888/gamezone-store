<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top" x-data="{ 
    showNotifications: false,
    notifications: [],
    unreadCount: 0,
    theme: 'light'
}">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand gaming-font fw-bold" href="{{ route('home') }}">
            <span class="gaming-gradient bg-gradient text-transparent bg-clip-text">GameZone</span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        {{ __('messages.home') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('topup') ? 'active' : '' }}" href="{{ route('topup') }}">
                        {{ __('messages.topup') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('accounts') ? 'active' : '' }}" href="{{ route('accounts') }}">
                        {{ __('messages.accounts') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('faq') ? 'active' : '' }}" href="{{ route('faq') }}">
                        {{ __('messages.faq') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">
                        {{ __('messages.contact') }}
                    </a>
                </li>
            </ul>

            <!-- Right Side -->
            <ul class="navbar-nav">
                <!-- Language Switcher -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        @if(app()->getLocale() === 'ar')
                            🇸🇦 العربية
                        @else
                            🇺🇸 English
                        @endif
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('language.switch', 'en') }}">
                                🇺🇸 English
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('language.switch', 'ar') }}">
                                🇸🇦 العربية
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Theme Toggle -->
                <li class="nav-item">
                    <button class="btn btn-link nav-link" @click="theme = theme === 'light' ? 'dark' : 'light'" title="{{ __('messages.toggle_theme') }}">
                        <span x-show="theme === 'light'">🌙</span>
                        <span x-show="theme === 'dark'">☀️</span>
                    </button>
                </li>

                <!-- Cart -->
                <li class="nav-item">
                    <a class="nav-link position-relative" href="{{ route('cart') }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count" style="display: none;">
                            0
                        </span>
                    </a>
                </li>

                @auth
                    <!-- Notifications -->
                    <li class="nav-item dropdown" x-data="{ open: false }">
                        <a class="nav-link position-relative" href="#" @click="open = !open" role="button">
                            <i class="fas fa-bell"></i>
                            <span x-show="unreadCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" x-text="unreadCount"></span>
                        </a>
                        <div x-show="open" @click.away="open = false" class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <h6 class="dropdown-header">{{ __('messages.notifications') }}</h6>
                            <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
                                <template x-for="notification in notifications" :key="notification.id">
                                    <a class="dropdown-item notification-item" href="#" x-text="notification.message"></a>
                                </template>
                                <div x-show="notifications.length === 0" class="dropdown-item text-muted text-center">
                                    {{ __('messages.no_notifications') }}
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center" href="{{ route('dashboard.notifications') }}">
                                {{ __('messages.view_all') }}
                            </a>
                        </div>
                    </li>

                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="{{ auth()->user()->avatar_url ?? asset('images/default-avatar.png') }}" 
                                 alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                            {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('dashboard.index') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    {{ __('messages.dashboard') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('dashboard.profile') }}">
                                    <i class="fas fa-user me-2"></i>
                                    {{ __('messages.profile') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('dashboard.orders') }}">
                                    <i class="fas fa-shopping-bag me-2"></i>
                                    {{ __('messages.my_orders') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('dashboard.support-tickets.index') }}">
                                    <i class="fas fa-headset me-2"></i>
                                    {{ __('messages.support') }}
                                </a>
                            </li>
                            @if(auth()->user()->hasRole(['super-admin', 'admin']))
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-cog me-2"></i>
                                        {{ __('messages.admin_panel') }}
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        {{ __('messages.logout') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <!-- Guest Links -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('messages.login') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-gaming btn-sm ms-2" href="{{ route('register') }}">{{ __('messages.register') }}</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<script>
    function toggleTheme() {
        const navbar = document.querySelector('nav');
        const themeIconLight = document.querySelector('span[x-show="theme === \'light\'"]');
        const themeIconDark = document.querySelector('span[x-show="theme === \'dark\'"]');

        if (navbar.classList.contains('navbar-light')) {
            navbar.classList.remove('navbar-light');
            navbar.classList.add('navbar-dark');
            navbar.style.backgroundColor = '#343a40';
            themeIconLight.style.display = 'none';
            themeIconDark.style.display = 'inline';
        } else {
            navbar.classList.remove('navbar-dark');
            navbar.classList.add('navbar-light');
            navbar.style.backgroundColor = 'white';
            themeIconLight.style.display = 'inline';
            themeIconDark.style.display = 'none';
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('navbar', () => ({
            showNotifications: false,
            notifications: [],
            unreadCount: 0,
            theme: 'light',

            init() {
                this.loadNotifications();
            },

            loadNotifications() {
                fetch('/api/v1/user/notifications', {
                    headers: {
                        'Authorization': 'Bearer {{ auth()->user()->createToken("navbar")->plainTextToken }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.notifications = data.data.slice(0, 5);
                        this.unreadCount = data.data.filter(n => !n.read_at).length;
                    }
                })
                .catch(error => console.error('Error loading notifications:', error));
            },

            markAsRead(notificationId) {
                fetch(`/api/v1/user/notifications/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': 'Bearer {{ auth()->user()->createToken("navbar")->plainTextToken }}',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(() => {
                    this.loadNotifications();
                })
                .catch(error => console.error('Error marking notification as read:', error));
            }
        }));
    });
</script>

<style>
.gaming-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.btn-gaming {
    background: linear-gradient(45deg, #667eea, #764ba2);
    border: none;
    color: white;
    transition: all 0.3s ease;
}

.btn-gaming:hover {
    background: linear-gradient(45deg, #764ba2, #667eea);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

.notification-dropdown {
    min-width: 300px;
}

.notification-item {
    white-space: normal;
    word-wrap: break-word;
}

.cart-count {
    font-size: 0.7rem;
}
</style>
