<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs(
            'profile.*',
            'password.update',
            'password.confirm',
            'verification.*',
            'logout',
            'private-media.avatar',
        )) {
            return $next($request);
        }

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Replace your temporary password before continuing.');
    }
}
