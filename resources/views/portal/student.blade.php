<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Student portal</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $student->user->name }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $student->admission_no }} - {{ $student->schoolClass->name ?? 'Class pending' }}</p>
            </div>
            @if ($children->isNotEmpty())
                <form method="GET" action="{{ route('portal.index') }}">
                    <select name="student" onchange="this.form.submit()" class="theme-input">
                        @foreach ($children as $child)
                            <option value="{{ $child->id }}" @selected($child->id === $student->id)>{{ $child->user->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-4">
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Lessons</div>
            <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $lessons->count() }}</div>
        </div>
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Assignments</div>
            <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $assignments->count() }}</div>
        </div>
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Result entries</div>
            <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $results->count() }}</div>
        </div>
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Outstanding fees</div>
            <div class="display-font mt-3 text-4xl font-bold text-slate-950">NGN {{ number_format((float) $invoices->sum('balance'), 2) }}</div>
        </div>
    </div>

    <section class="section-card mt-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Official reports and student record</h2>
                <p class="mt-2 text-sm text-slate-500">Open any approved term report, print it exactly as a school document, or print the student's full record dossier.</p>
            </div>
            <a href="{{ route('portal.record', $children->isNotEmpty() ? ['student' => $student->id] : []) }}" target="_blank" class="theme-button-secondary">Print student record</a>
        </div>

        <div class="mt-5 space-y-4">
            @forelse ($publishedReports as $publishedReport)
                <article class="rounded-3xl border border-slate-200 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $publishedReport->term->name }} - {{ $publishedReport->term->academicSession->name ?? 'No session' }}</div>
                            <div class="mt-1 text-sm text-slate-500">Average {{ $publishedReport->average_score !== null ? number_format((float) $publishedReport->average_score, 2).'%' : 'N/A' }} | Grade {{ $publishedReport->overall_grade ?: 'N/A' }} | Position {{ $publishedReport->class_position ?: 'N/A' }}</div>
                            <div class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-500">Published {{ $publishedReport->published_at?->format('M j, Y g:i A') }}</div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('portal.results.print', [$publishedReport->term]) }}{{ $children->isNotEmpty() ? '?student='.$student->id : '' }}" target="_blank" class="theme-button">Open report card</a>
                            <a href="{{ route('portal.results.print', [$publishedReport->term]) }}?layout=classic{{ $children->isNotEmpty() ? '&student='.$student->id : '' }}" target="_blank" class="theme-button-secondary">Classic one-page version</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No approved report card has been released to this portal yet. Admin can publish it here or enable checker-PIN access.</div>
            @endforelse
        </div>
    </section>

    <div class="mt-8 grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Recent lessons</h2>
            <div class="mt-5 space-y-4">
                @foreach ($lessons as $lesson)
                    <article class="rounded-3xl border border-slate-200 px-5 py-5">
                        <div class="font-semibold text-slate-900">{{ $lesson->title }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-500">{{ $lesson->subject->name }} - {{ $lesson->teacher->name }}</div>
                        @if ($lesson->summary)
                            <p class="mt-3 text-sm text-slate-500">{{ $lesson->summary }}</p>
                        @endif
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $lesson->body }}</p>
                        @if ($lesson->video_path)
                            <video controls preload="metadata" class="mt-4 w-full rounded-3xl border border-slate-200 bg-slate-950">
                                <source src="{{ asset($lesson->video_path) }}">
                            </video>
                        @elseif ($lesson->video_url)
                            <a href="{{ $lesson->video_url }}" target="_blank" class="mt-4 inline-flex text-sm font-semibold text-slate-900">Open video lesson</a>
                        @endif
                        @if (filled($lesson->note_images))
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($lesson->note_images as $image)
                                    <a href="{{ asset($image) }}" target="_blank" class="overflow-hidden rounded-3xl border border-slate-200">
                                        <img src="{{ asset($image) }}" alt="Lesson note image" class="h-52 w-full object-cover" />
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        @if ($lesson->resource_link)
                            <a href="{{ $lesson->resource_link }}" target="_blank" class="mt-3 inline-flex text-sm font-semibold text-slate-900">Open resource</a>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Subject performance</h2>
            <div class="mt-5 space-y-3">
                @forelse ($reportSummary as $subject => $summary)
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="font-semibold text-slate-900">{{ $subject }}</div>
                        <div class="text-sm text-slate-500">Average {{ $summary['average'] }} across {{ $summary['entries'] }} entries</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Results have not been entered yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-8 grid gap-8 xl:grid-cols-2">
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Assignments and submissions</h2>
            <div class="mt-5 space-y-4">
                @foreach ($assignments as $assignment)
                    <article class="rounded-3xl border border-slate-200 px-5 py-5">
                        <div class="font-semibold text-slate-900">{{ $assignment->title }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-500">{{ $assignment->subject->name }} - Due {{ optional($assignment->due_date)->format('M j, Y g:i A') ?: 'not set' }}</div>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $assignment->instructions }}</p>
                        @if (filled($assignment->attachment_images))
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($assignment->attachment_images as $image)
                                    <a href="{{ asset($image) }}" target="_blank" class="overflow-hidden rounded-3xl border border-slate-200">
                                        <img src="{{ asset($image) }}" alt="Assignment image" class="h-52 w-full object-cover" />
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        @if ($user->hasAnyRole(['student']))
                            <form method="POST" action="{{ route('portal.assignments.submit', $assignment) }}" class="mt-4 space-y-3">
                                @csrf
                                <textarea name="content" rows="3" placeholder="{{ $submissions->has($assignment->id) ? 'Update your submission' : 'Write your answer or submission note' }}" class="theme-input w-full"></textarea>
                                <button type="submit" class="theme-button">
                                    {{ $submissions->has($assignment->id) ? 'Update submission' : 'Submit assignment' }}
                                </button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Results and attendance</h2>
            <div class="mt-5 space-y-4">
                @foreach ($results as $result)
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="font-semibold text-slate-900">{{ $result->assessment->title }}</div>
                        <div class="text-sm text-slate-500">{{ $result->assessment->subject->name }} - {{ $result->assessment->term->name ?? 'No term' }}</div>
                        <div class="mt-2 text-sm font-bold text-slate-900">{{ $result->score }} {{ $result->grade ? '- '.$result->grade : '' }}</div>
                    </div>
                @endforeach

                <div class="mt-8">
                    <h3 class="display-font text-xl font-bold text-slate-950">Attendance history</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($attendance as $entry)
                            <div class="rounded-2xl border border-slate-200 px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $entry->attendance_date->format('M j, Y') }}</div>
                                <div class="text-sm text-slate-500">{{ $entry->status->label() }}{{ $entry->note ? ' - '.$entry->note : '' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if ($user->hasAnyRole(['student']))
        @php($attemptsByAssessment = $cbtAttempts->keyBy('assessment_id'))
        <section class="section-card mt-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">CBT exams and tests</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        @if ($cbtEnabled)
                            Start any active CBT from here. Your teacher questions, images, videos, and links will appear inside the exam screen.
                        @else
                            CBT is currently turned off by the school administrator.
                        @endif
                    </p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                @forelse ($cbtAssessments as $cbtAssessment)
                    @php($attempt = $attemptsByAssessment->get($cbtAssessment->id))
                    <article class="rounded-3xl border border-slate-200 px-5 py-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $cbtAssessment->title }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $cbtAssessment->subject->name }} | {{ $cbtAssessment->teacher->fullName() }}</div>
                                <div class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-500">
                                    {{ $cbtAssessment->cbtQuestions->count() }} question(s)
                                    |
                                    {{ $cbtAssessment->cbt_duration_minutes }} mins
                                    @if ($cbtAssessment->cbt_ends_at)
                                        | Ends {{ $cbtAssessment->cbt_ends_at->format('M j, Y g:i A') }}
                                    @endif
                                </div>
                                @if ($attempt)
                                    <div class="mt-3 text-sm text-slate-600">
                                        Status: {{ ucfirst($attempt->status) }}
                                        @if ($attempt->status === 'graded' || ($attempt->status === 'submitted' && $cbtAssessment->cbt_show_results))
                                            | Score: {{ number_format((float) $attempt->total_score, 2) }} / {{ number_format((float) $cbtAssessment->total_score, 2) }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                            @if (! $attempt || $attempt->status === 'in_progress')
                                <a href="{{ route('portal.cbt.show', $cbtAssessment) }}" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white">
                                    {{ $attempt ? 'Continue CBT' : 'Start CBT' }}
                                </a>
                            @else
                                <div class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Submitted</div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No active CBT test or exam is available for your class right now.</div>
                @endforelse
            </div>
        </section>
    @endif

    <section class="section-card mt-8">
        <h2 class="display-font text-2xl font-bold text-slate-950">Fees and payment history</h2>
        <div class="mt-6 grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
            <div class="space-y-4" x-data="{ selectedInvoices: [], totals: @js($invoices->mapWithKeys(fn ($invoice) => [$invoice->id => (float) $invoice->balance])) }">
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Selected fee items</div>
                            <div class="mt-2 text-sm text-slate-600">Tick the fee items you want to pay. You can pay one item or multiple items at once.</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Selected total</div>
                            <div class="display-font mt-2 text-3xl font-bold text-slate-950" x-text="`NGN ${selectedInvoices.reduce((sum, id) => sum + Number(totals[id] || 0), 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></div>
                        </div>
                    </div>
                    <form method="POST" class="mt-5 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        @csrf
                        <template x-for="invoiceId in selectedInvoices" :key="invoiceId">
                            <input type="hidden" name="invoice_ids[]" :value="invoiceId">
                        </template>
                        <button type="submit" formaction="{{ route('payments.selection.checkout', 'paystack') }}" class="theme-button" :disabled="selectedInvoices.length === 0">Pay selected with Paystack</button>
                        <button type="submit" formaction="{{ route('payments.selection.checkout', 'palmpay') }}" class="theme-button-secondary" :disabled="selectedInvoices.length === 0">Pay selected with PalmPay</button>
                    </form>
                </div>

                @foreach ($invoices as $invoice)
                    <div class="rounded-3xl border border-slate-200 px-5 py-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $invoice->invoice_no }}</div>
                                <div class="text-sm text-slate-500">{{ $invoice->feeItem->name ?? 'Custom invoice' }} - Due {{ optional($invoice->due_date)->format('M j, Y') ?: 'open' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="display-font text-2xl font-bold text-slate-950">NGN {{ number_format((float) $invoice->balance, 2) }}</div>
                                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $invoice->status }}</div>
                            </div>
                        </div>
                        <div class="mt-3 text-sm text-slate-600">Paid so far: NGN {{ number_format((float) $invoice->amount_paid, 2) }}</div>
                        @if ((float) $invoice->balance > 0)
                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <label class="inline-flex items-center gap-3 rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">
                                    <input type="checkbox" value="{{ $invoice->id }}" x-model="selectedInvoices" class="rounded border-slate-300" />
                                    Select this fee item
                                </label>
                                <form method="POST" action="{{ route('payments.checkout', [$invoice, 'paystack']) }}">
                                    @csrf
                                    <button type="submit" class="theme-button">Pay with Paystack</button>
                                </form>
                                <form method="POST" action="{{ route('payments.checkout', [$invoice, 'palmpay']) }}">
                                    @csrf
                                    <button type="submit" class="theme-button-secondary">Pay with PalmPay</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="space-y-4">
                @foreach ($payments as $payment)
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="font-semibold text-slate-900">{{ $payment->provider->label() }}</div>
                        <div class="text-sm text-slate-500">{{ $payment->reference }}</div>
                        <div class="mt-2 text-sm font-bold text-slate-900">NGN {{ number_format((float) $payment->amount, 2) }} - {{ $payment->status->label() }}</div>
                        <div class="mt-3 flex flex-wrap gap-3">
                            <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="text-sm font-semibold text-[color:var(--theme-primary)]">Open receipt</a>
                            <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="text-sm text-slate-500">Print / Save as PDF</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-app-layout>
