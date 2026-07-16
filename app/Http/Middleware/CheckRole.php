<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            // return response()->json(['message' => 'Unauthenticated.'], 401);
            return redirect()->route('admin.login');
        }

        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have the required role.'
            ], 403);
        }

        return $next($request);
    }
}