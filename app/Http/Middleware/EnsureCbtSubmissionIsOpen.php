<?php

namespace App\Http\Middleware;

use App\Models\Assessment;
use App\Models\CbtAttempt;
use App\Models\Setting;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCbtSubmissionIsOpen
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $assessment = $request->route('assessment');
        abort_unless($assessment instanceof Assessment && $assessment->is_cbt, 404);

        $student = $request->user()?->studentProfile()->first();
        abort_unless($student, 403);
        abort_unless($assessment->school_class_id === $student->school_class_id, 403);
        abort_unless((string) Setting::getValue('cbt_enabled', '1') === '1', 403);
        abort_unless($assessment->cbt_is_active, 403);

        if ($assessment->cbt_starts_at && now()->lt($assessment->cbt_starts_at)) {
            return redirect()->route('portal.index')->with('status', 'This CBT exam has not started yet.');
        }

        if ($assessment->cbt_ends_at && now()->gt($assessment->cbt_ends_at)) {
            return redirect()->route('portal.index')->with('status', 'This CBT exam is no longer available.');
        }

        $attempt = CbtAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('portal.index')->with('status', 'This CBT exam has already been submitted.');
        }

        if ($attempt->expires_at && now()->gt($attempt->expires_at)) {
            $attempt->update(['status' => 'expired']);

            return redirect()->route('portal.index')->with('status', 'This CBT exam time has expired.');
        }

        return $next($request);
    }
}
