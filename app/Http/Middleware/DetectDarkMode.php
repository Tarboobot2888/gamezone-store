<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectDarkMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $theme = $request->cookie('theme', 'dark');
        
        if (auth()->check() && auth()->user()->theme_preference !== $theme) {
            auth()->user()->update(['theme_preference' => $theme]);
        }

        view()->share('currentTheme', $theme);

        return $next($request);
    }
}
