<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Legacy "role" column check, kept for routes that still use it.
     *
     * Fixed: a guest used to crash with an error (null->role) instead of a clean 403, the
     * comparison was loose, and the superadmin (identified by system_account) was not let through.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isSuperAdmin() && ! in_array($user->role, $roles, true))) {
            abort(403);
        }

        return $next($request);
    }
}
