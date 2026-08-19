<?php

namespace App\Http\Controllers;

use App\Models\StaffProfile;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAccountStatusController extends Controller
{
    public function toggleStudent(Request $request, Student $student): RedirectResponse
    {
        $currentStatus = (string) ($student->status ?: $student->user?->status ?: 'active');
        $nextStatus = $currentStatus === 'inactive' ? 'active' : 'inactive';

        DB::transaction(function () use ($student, $nextStatus): void {
            $student->update(['status' => $nextStatus]);
            $student->user?->update(['status' => $nextStatus]);
        });

        $verb = $nextStatus === 'active' ? 'activated' : 'deactivated';

        return redirect()
            ->route('admin.students.index', $this->studentRedirectParameters($request->all()))
            ->with('status', "Student account {$verb}.");
    }

    public function toggleStaff(Request $request, StaffProfile $staffProfile): RedirectResponse
    {
        $currentStatus = (string) ($staffProfile->status ?: $staffProfile->user?->status ?: 'active');
        $nextStatus = $currentStatus === 'inactive' ? 'active' : 'inactive';

        DB::transaction(function () use ($staffProfile, $nextStatus): void {
            $staffProfile->update(['status' => $nextStatus]);
            $staffProfile->user?->update(['status' => $nextStatus]);
        });

        $verb = $nextStatus === 'active' ? 'activated' : 'deactivated';

        return redirect()
            ->route('admin.staff.index', $this->staffRedirectParameters($request->all()))
            ->with('status', "Staff account {$verb}.");
    }

    /** @param array<string, mixed> $inputs */
    protected function studentRedirectParameters(array $inputs): array
    {
        $parameters = [];

        foreach (['search', 'classSlug', 'view'] as $key) {
            $value = trim((string) ($inputs[$key] ?? $inputs["redirect_{$key}"] ?? ''));

            if ($value !== '') {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    /** @param array<string, mixed> $inputs */
    protected function staffRedirectParameters(array $inputs): array
    {
        $parameters = [];

        foreach (['search', 'department', 'view'] as $key) {
            $value = trim((string) ($inputs[$key] ?? $inputs["redirect_{$key}"] ?? ''));

            if ($value !== '') {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }
}
