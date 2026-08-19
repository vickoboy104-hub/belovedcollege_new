<?php

namespace App\Http\Middleware;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ValidateAcademicSetupInput
{
    public function handle(Request $request, Closure $next): Response
    {
        $creatingCurrentSession = $request->routeIs('admin.sessions.store') && $request->boolean('is_current');
        $previousCurrentSessionId = null;

        if ($request->routeIs('admin.sessions.store')) {
            $this->validateSession($request);
        }

        if ($creatingCurrentSession) {
            $previousCurrentSessionId = AcademicSession::query()
                ->where('is_current', true)
                ->value('id');
        }

        if ($request->routeIs('admin.terms.store')) {
            $this->validateTerm($request);
        }

        if ($request->routeIs('admin.classes.store', 'admin.classes.update')) {
            $this->validateClass($request);
        }

        if ($request->routeIs('admin.subjects.store')) {
            $this->validateSubject($request);
        }

        $response = $next($request);

        if ($creatingCurrentSession) {
            $currentSessionId = AcademicSession::query()
                ->where('is_current', true)
                ->value('id');

            if ($currentSessionId && (int) $currentSessionId !== (int) $previousCurrentSessionId) {
                Term::query()
                    ->where('is_current', true)
                    ->where('academic_session_id', '!=', $currentSessionId)
                    ->update(['is_current' => false]);
            }
        }

        return $response;
    }

    protected function validateSession(Request $request): void
    {
        if (! $request->boolean('is_current')) {
            return;
        }

        $openCurrentSession = AcademicSession::query()
            ->where('is_current', true)
            ->whereNull('closed_at')
            ->first();

        if ($openCurrentSession) {
            throw ValidationException::withMessages([
                'is_current' => 'Close the current academic session before activating a new one.',
            ]);
        }
    }

    protected function validateTerm(Request $request): void
    {
        $sessionId = $request->input('academic_session_id');
        if (! $sessionId) {
            return;
        }

        $session = AcademicSession::query()->find($sessionId);
        if (! $session) {
            return;
        }

        if ($session->closed_at !== null) {
            throw ValidationException::withMessages([
                'academic_session_id' => 'You cannot add a term to a closed academic session.',
            ]);
        }

        $name = trim((string) $request->input('name', ''));
        if ($name !== '' && Term::query()
            ->where('academic_session_id', $session->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->exists()) {
            throw ValidationException::withMessages([
                'name' => 'A term with this name already exists in the selected academic session.',
            ]);
        }

        $startDate = $this->parseDate($request->input('start_date'));
        $endDate = $this->parseDate($request->input('end_date'));

        if ($startDate && $startDate->lt($session->start_date->startOfDay())) {
            throw ValidationException::withMessages([
                'start_date' => 'The term start date cannot be earlier than the academic session start date.',
            ]);
        }

        if ($endDate && $endDate->gt($session->end_date->endOfDay())) {
            throw ValidationException::withMessages([
                'end_date' => 'The term end date cannot be later than the academic session end date.',
            ]);
        }

        if ($request->boolean('is_current') && ! $session->is_current) {
            throw ValidationException::withMessages([
                'academic_session_id' => 'A current term must belong to the current academic session.',
            ]);
        }
    }

    protected function validateClass(Request $request): void
    {
        $name = trim((string) $request->input('name', ''));
        $section = trim((string) $request->input('section', ''));

        if ($name !== '') {
            $duplicate = SchoolClass::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->where(function (Builder $query) use ($section): void {
                    if ($section === '') {
                        $query->whereNull('section')->orWhere('section', '');
                        return;
                    }

                    $query->whereRaw('LOWER(section) = ?', [mb_strtolower($section)]);
                });

            $currentClass = $request->route('schoolClass');
            if ($currentClass instanceof SchoolClass) {
                $duplicate->where('id', '!=', $currentClass->getKey());
            }

            if ($duplicate->exists()) {
                throw ValidationException::withMessages([
                    'name' => 'This class and section combination already exists.',
                ]);
            }
        }

        $teacherId = $request->input('class_teacher_id');
        if ($teacherId) {
            $teacher = User::query()->find($teacherId);
            if ($teacher && strtolower((string) $teacher->status) !== 'active') {
                throw ValidationException::withMessages([
                    'class_teacher_id' => 'Only an active staff account can be assigned as a class teacher.',
                ]);
            }
        }
    }

    protected function validateSubject(Request $request): void
    {
        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            return;
        }

        if (Subject::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
            throw ValidationException::withMessages([
                'name' => 'A subject with this name already exists.',
            ]);
        }
    }

    protected function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse((string) $value);
        } catch (Throwable) {
            return null;
        }
    }
}
