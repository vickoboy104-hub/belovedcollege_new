<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ValidatePeopleManagementInput
{
    /** @var array<int, string> */
    protected const STUDENT_ROUTES = [
        'admin.students.store',
        'admin.students.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (in_array($routeName, self::STUDENT_ROUTES, true)) {
            $this->validateStudentInput($request, $routeName);
        }

        if ($routeName === 'admin.staff.update') {
            $this->validateStatus($request);
        }

        return $next($request);
    }

    protected function validateStudentInput(Request $request, ?string $routeName): void
    {
        if ($routeName === 'admin.students.update') {
            $this->validateStatus($request);
        }

        $parentEmail = trim((string) $request->input('parent_email', ''));

        if ($parentEmail === '') {
            return;
        }

        $studentEmail = trim((string) $request->input('email', ''));

        if ($studentEmail !== '' && strcasecmp($parentEmail, $studentEmail) === 0) {
            throw ValidationException::withMessages([
                'parent_email' => 'The parent email must be different from the student email.',
            ]);
        }

        $existingUser = User::query()->where('email', $parentEmail)->first();

        if ($existingUser && ! $existingUser->hasAnyRole(UserRole::Parent)) {
            throw ValidationException::withMessages([
                'parent_email' => 'That email already belongs to a non-parent portal account. Use a different parent email.',
            ]);
        }
    }

    protected function validateStatus(Request $request): void
    {
        if (! $request->filled('status')) {
            return;
        }

        if (! in_array($request->string('status')->toString(), ['active', 'inactive'], true)) {
            throw ValidationException::withMessages([
                'status' => 'The selected account status is invalid.',
            ]);
        }
    }
}
