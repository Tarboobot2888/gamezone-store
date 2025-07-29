<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your email address is not verified.',
                    'redirect' => route('verification.notice')
                ], 403);
            }
            
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
