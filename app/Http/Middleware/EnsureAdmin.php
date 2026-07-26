<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects any Sanctum-authenticated caller that isn't an Admin — closes the
 * gap where `auth:sanctum` alone accepted any valid token regardless of role.
 */
class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof Admin) {
            abort(403, 'This action requires an admin account.');
        }

        return $next($request);
    }
}
