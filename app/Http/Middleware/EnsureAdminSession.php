<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the browser-facing admin panel.
 *
 * Distinct from EnsureAdmin, which requires the caller to be an
 * App\Models\Admin authenticated by a Sanctum token — that model isn't
 * reachable through the `web` session guard at all, so it can't guard
 * Inertia routes. AdminSeeder documents the intended bridge: the same person
 * exists in `admins` (API token login) and in `users` with is_admin = true
 * (the shared browser /login page). This checks the latter.
 */
class EnsureAdminSession
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if($user === null, 403);
        abort_unless($user->isAdmin(), 403, 'This area is for administrators.');
        abort_if($user->isSuspended(), 403, 'This account is suspended.');

        return $next($request);
    }
}
