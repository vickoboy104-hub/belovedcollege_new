<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\StaffProfile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ProtectStaffAdministration
{
    /** @var array<int, string> */
    protected const PROTECTED_ROUTES = [
        'admin.staff.store',
        'admin.staff.password.reset',
        'admin.staff.update',
        'admin.staff.deactivate',
        'admin.staff.destroy',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (! in_array($routeName, self::PROTECTED_ROUTES, true)) {
            return $next($request);
        }

        $actor = $request->user();

        if (! $actor) {
            return $next($request);
        }

        $target = $request->route('staffProfile');

        if ($target !== null && ! $target instanceof StaffProfile) {
            $target = StaffProfile::query()->with('user')->find($target);
        }

        if ($target instanceof StaffProfile) {
            $target->loadMissing('user');
        }

        if ($actor->hasAnyRole(UserRole::Principal)) {
            if ($request->string('role')->toString() === UserRole::Admin->value) {
                throw ValidationException::withMessages([
                    'role' => 'Only an administrator can assign the administrator role.',
                ]);
            }

            abort_if(
                $target?->user?->hasAnyRole(UserRole::Admin),
                403,
                'Administrator accounts can only be managed by an administrator.'
            );
        }

        if ($target?->user && $actor->is($target->user)) {
            abort_if(
                in_array($routeName, ['admin.staff.deactivate', 'admin.staff.destroy'], true),
                403,
                'You cannot deactivate or delete your own account.'
            );

            if ($routeName === 'admin.staff.update') {
                $currentRole = $target->user->role instanceof UserRole
                    ? $target->user->role->value
                    : (string) $target->user->role;

                if ($request->filled('role') && $request->string('role')->toString() !== $currentRole) {
                    throw ValidationException::withMessages([
                        'role' => 'You cannot change your own portal role from the staff management screen.',
                    ]);
                }

                if ($request->filled('status') && $request->string('status')->toString() !== 'active') {
                    throw ValidationException::withMessages([
                        'status' => 'You cannot deactivate your own account from the staff management screen.',
                    ]);
                }
            }
        }

        return $next($request);
    }
}
