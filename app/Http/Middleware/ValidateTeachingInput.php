<?php

namespace App\Http\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ValidateTeachingInput
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('teacher.assignments.store')) {
            $status = trim((string) $request->input('status', ''));

            if ($status !== 'published') {
                throw ValidationException::withMessages([
                    'status' => 'Assignments created from the teaching workspace must be published.',
                ]);
            }
        }

        if ($request->routeIs('teacher.attendance.store')) {
            $attendanceDate = $this->parseDate($request->input('attendance_date'));

            if ($attendanceDate && $attendanceDate->isAfter(CarbonImmutable::today())) {
                throw ValidationException::withMessages([
                    'attendance_date' => 'Attendance cannot be recorded for a future date.',
                ]);
            }
        }

        return $next($request);
    }

    protected function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse((string) $value)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }
}
