<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage in routes: middleware('role:admin')  or  middleware('role:educator,admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! in_array($user->role->name, $roles)) {
            return response()->json([
                'message' => 'Access denied. Required role: ' . implode(' or ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
