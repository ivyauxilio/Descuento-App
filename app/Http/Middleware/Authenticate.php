<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API requests, return 401
        if ($request->expectsJson()) {
            return null;
        }

        // Check if the request is for admin routes
       if ($request->is('admin/*') || $request->is('admin')) {
            return route('admin.login');
        }

        // Default redirect for web routes
        return route('login');
    }
}