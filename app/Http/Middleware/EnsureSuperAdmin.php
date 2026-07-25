<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects any admin that isn't a superadmin — for actions like creating
 * other admin accounts that regular admins shouldn't be able to do.
 */
class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof Admin || ! $request->user()->isSuperAdmin()) {
            abort(403, 'This action requires a superadmin account.');
        }

        return $next($request);
    }
}
