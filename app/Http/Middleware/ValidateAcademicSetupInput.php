<?php

namespace App\Http\Middleware;

use App\Models\AcademicSession;
use App\Models\Term;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ValidateAcademicSetupInput
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs('admin.terms.store')) {
            return $next($request);
        }

        $sessionId = $request->input('academic_session_id');
        if (! $sessionId) {
            return $next($request);
        }

        $session = AcademicSession::query()->find($sessionId);
        if (! $session) {
            return $next($request);
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

        return $next($request);
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
