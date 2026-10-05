<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\TransientToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperadmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() === null) {
            Auth::shouldUse('web');
        }

        $user = $request->bearerToken()
            ? $request->user()
            : (Auth::guard('web')->user() ?? $request->user());
        abort_unless($user?->isSuperAdmin(), 403);

        $token = $user->currentAccessToken();
        if ($token !== null && !$token instanceof TransientToken) {
            abort_unless($token->can('roles:manage'), 403);
        }

        return $next($request);
    }
}