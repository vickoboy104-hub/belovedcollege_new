<?php

namespace App\Http\Middleware;

use App\Models\FeeItem;
use App\Models\Student;
use App\Models\Term;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ValidateFinanceInput
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('admin.fee-items.store')) {
            $this->validateFeeItem($request);
        }

        if ($request->routeIs('admin.invoices.store')) {
            $this->validateInvoice($request);
        }

        return $next($request);
    }

    protected function validateFeeItem(Request $request): void
    {
        $termId = $request->input('term_id');
        if (! $termId) {
            return;
        }

        $term = Term::query()->find($termId);
        if (! $term) {
            return;
        }

        $sessionId = $request->input('academic_session_id');

        if ($sessionId && (int) $sessionId !== (int) $term->academic_session_id) {
            throw ValidationException::withMessages([
                'term_id' => 'The selected term does not belong to the selected academic session.',
            ]);
        }

        if (! $sessionId) {
            $request->merge(['academic_session_id' => $term->academic_session_id]);
        }
    }

    protected function validateInvoice(Request $request): void
    {
        $studentId = $request->input('student_id');
        $classId = $request->input('school_class_id');

        if ($studentId && $classId) {
            throw ValidationException::withMessages([
                'student_id' => 'Choose either one student or one whole class for an invoice, not both.',
            ]);
        }

        $feeItem = $request->input('fee_item_id')
            ? FeeItem::query()->find($request->input('fee_item_id'))
            : null;

        $hasOverride = $request->filled('amount_due');
        $effectiveAmount = $hasOverride
            ? (float) $request->input('amount_due')
            : (float) ($feeItem?->amount ?? 0);

        if ($effectiveAmount <= 0) {
            throw ValidationException::withMessages([
                'amount_due' => 'Select a fee item with a positive amount or enter a positive invoice amount.',
            ]);
        }

        if (! $feeItem?->school_class_id) {
            return;
        }

        if ($classId && (int) $classId !== (int) $feeItem->school_class_id) {
            throw ValidationException::withMessages([
                'fee_item_id' => 'The selected fee item does not apply to the selected class.',
            ]);
        }

        if ($studentId) {
            $student = Student::query()->find($studentId);
            if ($student && (int) $student->school_class_id !== (int) $feeItem->school_class_id) {
                throw ValidationException::withMessages([
                    'fee_item_id' => 'The selected fee item does not apply to this student’s current class.',
                ]);
            }
        }
    }
}
