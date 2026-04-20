<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        $allowedRoles = collect($roles)
            ->map(fn (string $role) => UserRole::tryFrom($role)?->value ?? $role)
            ->all();

        abort_unless($user->hasAnyRole($allowedRoles), 403);

        return $next($request);
    }
}
