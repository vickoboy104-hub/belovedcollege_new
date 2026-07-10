<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$attempt->student->user->fullName()" eyebrow="CBT Review">
            <x-slot name="description">
                {{ $attempt->assessment->title }} &bull; {{ $attempt->assessment->subject->name }} &bull; {{ $attempt->assessment->schoolClass->name }}
            </x-slot>
            <x-slot name="actions">
                <x-action-button :href="route('teacher.cbt.assessments.show', $attempt->assessment)" variant="secondary" icon="arrow-left">
                    Back to CBT builder
                </x-action-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="space-y-8">
        <!-- Stat Cards Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Status" :value="ucfirst($attempt->status)" icon="info" :accent="$attempt->status === 'completed' ? 'green' : 'orange'" />
            <x-stat-card label="Objective Score" :value="number_format((float) $attempt->objective_score, 2)" icon="check" accent="blue" />
            <x-stat-card label="Theory Score" :value="number_format((float) $attempt->theory_score, 2)" icon="edit" accent="purple" />
            <x-stat-card label="Total Score" :value="number_format((float) $attempt->total_score, 2)" icon="award" accent="green" />
        </div>

        <!-- Answers Panel -->
        <x-dashboard-card title="Exam Responses" subtitle="Grade and review the student's individual answers." icon="book-open" accent="blue">
            <div class="space-y-6">
                @foreach ($attempt->answers as $index => $answer)
                    <article class="rounded-[18px] border border-slate-200 bg-slate-50/50 p-5 shadow-sm hover:border-[#fbbf24] transition-all">
                        <div class="flex items-center justify-between gap-3 flex-wrap border-b border-slate-100 pb-3 mb-4">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#071833] text-[10px] font-black text-white shrink-0">
                                    {{ $index + 1 }}
                                </span>
                                <x-status-badge :status="$answer->question->question_type === 'objective' ? 'open' : 'marked'" :label="ucfirst($answer->question->question_type)" />
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-100 border border-slate-200 rounded-full px-2.5 py-0.5">
                                {{ number_format((float) $answer->question->points, 2) }} Mark(s) Available
                            </span>
                        </div>

                        <!-- Question Text -->
                        <p class="whitespace-pre-line text-sm font-semibold text-slate-800 leading-relaxed">{{ $answer->question->prompt }}</p>

                        <!-- Answer Review Block -->
                        @if ($answer->question->question_type === 'objective')
                            <div class="mt-4 rounded-xl border p-4 shadow-sm {{ $answer->is_correct ? 'border-emerald-200 bg-emerald-50 text-emerald-950' : 'border-rose-200 bg-rose-50 text-rose-950' }}">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Selected Option</div>
                                <div class="text-sm font-bold flex items-center gap-2">
                                    <x-app-icon :name="$answer->is_correct ? 'check-circle' : 'x-circle'" class="h-5 w-5 shrink-0 {{ $answer->is_correct ? 'text-emerald-600' : 'text-rose-600' }}" />
                                    <span>{{ $answer->selectedOption?->option_text ?: 'No option selected' }}</span>
                                </div>
                                <div class="mt-2 text-xs font-bold uppercase tracking-wider {{ $answer->is_correct ? 'text-emerald-700' : 'text-rose-700' }}">
                                    Grading consequence: {{ $answer->is_correct ? '+' . number_format((float) $answer->question->points, 2) : '0.00' }} points
                                </div>
                            </div>
                        @else
                            <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Student Submitted Answer</div>
                                <p class="text-sm font-bold text-slate-800 whitespace-pre-line leading-relaxed">
                                    {{ $answer->answer_text ?: 'No answer submitted.' }}
                                </p>
                            </div>

                            @if ($answer->question->theory_sample_answer)
                                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs leading-relaxed text-slate-600 shadow-inner">
                                    <div class="font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                                        <x-app-icon name="file-text" class="h-4 w-4 text-slate-500" />
                                        <span>Teacher Grading Guide / Sample Answer:</span>
                                    </div>
                                    <p class="whitespace-pre-line">{{ $answer->question->theory_sample_answer }}</p>
                                </div>
                            @endif

                            <!-- Grading Form -->
                            <form method="POST" action="{{ route('teacher.cbt.answers.grade', $answer) }}" class="mt-4 border-t border-slate-100 pt-4 flex flex-col md:flex-row items-end gap-3">
                                @csrf
                                <div class="w-full md:w-36">
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wide mb-1">Awarded Score</label>
                                    <input name="awarded_score" type="number" step="0.01" min="0" max="{{ $answer->question->points }}" value="{{ $answer->awarded_score }}" class="theme-input w-full text-xs py-2 font-bold" required />
                                </div>
                                <div class="flex-1 w-full">
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wide mb-1">Optional Feedback</label>
                                    <input name="feedback" value="{{ $answer->feedback }}" placeholder="Write feedback for the theory answer..." class="theme-input w-full text-xs py-2" />
                                </div>
                                <x-action-button type="submit" variant="accent" icon="save" class="shrink-0 w-full md:w-auto !py-2.5">
                                    Save Grade
                                </x-action-button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        </x-dashboard-card>
    </div>
</x-app-layout>
