<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\StaffProfile;
use App\Models\Student;
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
        $existingParent = null;
        $existingParentStatus = null;

        if ($routeName === 'admin.students.store' && ! $request->exists('admission_no')) {
            // Student registration supports automatic admission-number generation.
            // Normalize an omitted optional field so the existing controller can
            // safely distinguish it from a supplied value without indexing a
            // missing validated-data key.
            $request->merge(['admission_no' => null]);
        }

        if ($routeName === 'admin.staff.store' && ! $request->exists('employee_no')) {
            // Staff registration also supports automatic ID generation. Normalize
            // the omitted optional field so the controller can safely fall back to
            // generateEmployeeNumber().
            $request->merge(['employee_no' => null]);
        }

        if ($routeName === 'admin.students.update' && ! $request->exists('status')) {
            $student = $request->route('student');

            if ($student instanceof Student) {
                $student->loadMissing('user');
                $request->merge([
                    'status' => $student->status ?? $student->user?->status ?? 'active',
                ]);
            }
        }

        if ($routeName === 'admin.staff.update' && ! $request->exists('status')) {
            $staffProfile = $request->route('staffProfile');

            if ($staffProfile instanceof StaffProfile) {
                $staffProfile->loadMissing('user');
                $request->merge([
                    'status' => $staffProfile->status ?? $staffProfile->user?->status ?? 'active',
                ]);
            }
        }

        if (in_array($routeName, self::STUDENT_ROUTES, true)) {
            $this->validateStudentInput($request, $routeName);

            $parentEmail = trim((string) $request->input('parent_email', ''));
            if ($parentEmail !== '') {
                $candidate = User::query()->where('email', $parentEmail)->first();
                if ($candidate?->hasAnyRole(UserRole::Parent)) {
                    $existingParent = $candidate;
                    $existingParentStatus = (string) $candidate->status;
                }
            }
        }

        if ($routeName === 'admin.staff.update') {
            $this->validateStatus($request);
        }

        $response = $next($request);

        // Linking or editing a child must not silently reactivate an existing
        // parent portal account. Parent access has its own lifecycle and the
        // student form does not expose a parent-status control.
        if ($existingParent && $existingParentStatus !== null) {
            $freshParent = $existingParent->fresh();
            if ($freshParent && (string) $freshParent->status !== $existingParentStatus) {
                $freshParent->updateQuietly(['status' => $existingParentStatus]);
            }
        }

        return $response;
    }

    protected function validateStudentInput(Request $request, ?string $routeName): void
    {
        if ($routeName === 'admin.students.update') {
            $this->validateStatus($request);
        }

        $parentEmail = trim((string) $request->input('parent_email', ''));
        $parentName = trim((string) $request->input('parent_name', ''));
        $parentPhone = trim((string) $request->input('parent_phone', ''));

        if ($routeName === 'admin.students.store' && $parentEmail === '' && ($parentName !== '' || $parentPhone !== '')) {
            throw ValidationException::withMessages([
                'parent_email' => 'A parent email is required to create and link a parent portal account.',
            ]);
        }

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

        if (! $existingUser && $parentName === '') {
            throw ValidationException::withMessages([
                'parent_name' => 'Enter the parent or guardian name when creating a new parent portal account.',
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
