<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title>{{ config('app.name', 'GameZone') }}</title>
    @vite(['resources/sass/app.scss'])
</head>
<body>
    <div id="splash-screen" class="splash-screen">
        <div class="logo-animation">
            <svg width="120" height="120" viewBox="0 0 120 120" class="text-white">
                <defs>
                    <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#ffffff;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#e2e8f0;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <!-- Game Controller Shape -->
                <rect x="20" y="45" width="80" height="30" rx="15" fill="url(#logoGradient)"/>
                <!-- D-Pad -->
                <rect x="35" y="55" width="12" height="4" rx="2" fill="#6366f1"/>
                <rect x="39" y="51" width="4" height="12" rx="2" fill="#6366f1"/>
                <!-- Action Buttons -->
                <circle cx="75" cy="57" r="4" fill="#10b981"/>
                <circle cx="85" cy="57" r="4" fill="#f59e0b"/>
                <circle cx="80" cy="52" r="4" fill="#ef4444"/>
                <circle cx="80" cy="62" r="4" fill="#06b6d4"/>
                <!-- Handles -->
                <ellipse cx="15" cy="70" rx="12" ry="20" fill="url(#logoGradient)"/>
                <ellipse cx="105" cy="70" rx="12" ry="20" fill="url(#logoGradient)"/>
                <!-- Glow Effect -->
                <rect x="20" y="45" width="80" height="30" rx="15" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"/>
            </svg>
        </div>
        
        <h1 class="text-white mb-4 fw-bold" style="font-size: 2.5rem;">GameZone</h1>
        <p class="text-white-50 mb-4">{{ __('Loading your gaming experience...') }}</p>
        
        <div class="loading-bar">
            <div class="progress"></div>
        </div>
        
        <div class="mt-4">
            <div class="spinner-border text-white" role="status">
                <span class="visually-hidden">{{ __('Loading...') }}</span>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect after animation
        setTimeout(() => {
            const splash = document.getElementById('splash-screen');
            splash.classList.add('fade-out');
            
            setTimeout(() => {
                window.location.href = '{{ route("home") }}';
            }, 500);
        }, 3000);
        
        // Skip on click
        document.getElementById('splash-screen').addEventListener('click', () => {
            window.location.href = '{{ route("home") }}';
        });
    </script>
</body>
</html>
