<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">CBT review</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $attempt->student->user->fullName() }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $attempt->assessment->title }} | {{ $attempt->assessment->subject->name }} | {{ $attempt->assessment->schoolClass->name }}</p>
            </div>
            <a href="{{ route('teacher.cbt.assessments.show', $attempt->assessment) }}" class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Back to CBT builder</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Status</div>
            <div class="display-font mt-3 text-3xl font-bold text-slate-950">{{ ucfirst($attempt->status) }}</div>
        </div>
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Objective</div>
            <div class="display-font mt-3 text-3xl font-bold text-slate-950">{{ number_format((float) $attempt->objective_score, 2) }}</div>
        </div>
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Theory</div>
            <div class="display-font mt-3 text-3xl font-bold text-slate-950">{{ number_format((float) $attempt->theory_score, 2) }}</div>
        </div>
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Total</div>
            <div class="display-font mt-3 text-3xl font-bold text-slate-950">{{ number_format((float) $attempt->total_score, 2) }}</div>
        </div>
    </div>

    <section class="section-card mt-8">
        <h2 class="display-font text-2xl font-bold text-slate-950">Answers</h2>
        <div class="mt-5 space-y-5">
            @foreach ($attempt->answers as $answer)
                <article class="rounded-3xl border border-slate-200 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ ucfirst($answer->question->question_type) }} | {{ number_format((float) $answer->question->points, 2) }} mark(s)</div>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $answer->question->prompt }}</p>
                    @if ($answer->question->question_type === 'objective')
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm">
                            <div class="font-semibold text-slate-900">Selected answer</div>
                            <div class="mt-2 text-slate-700">{{ $answer->selectedOption?->option_text ?: 'No option selected' }}</div>
                            <div class="mt-2 text-sm {{ $answer->is_correct ? 'text-emerald-700' : 'text-rose-700' }}">{{ $answer->is_correct ? 'Correct' : 'Incorrect' }}</div>
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-700">
                            <div class="font-semibold text-slate-900">Student answer</div>
                            <p class="mt-2 whitespace-pre-line">{{ $answer->answer_text ?: 'No answer submitted.' }}</p>
                        </div>
                        @if ($answer->question->theory_sample_answer)
                            <div class="mt-4 rounded-2xl border border-slate-200 px-4 py-4 text-sm leading-7 text-slate-600">
                                <div class="font-semibold text-slate-900">Teacher guide</div>
                                <p class="mt-2 whitespace-pre-line">{{ $answer->question->theory_sample_answer }}</p>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('teacher.cbt.answers.grade', $answer) }}" class="mt-4 grid gap-4 md:grid-cols-[160px,1fr,160px]">
                            @csrf
                            <input name="awarded_score" type="number" step="0.01" min="0" max="{{ $answer->question->points }}" value="{{ $answer->awarded_score }}" class="rounded-2xl border border-slate-300 px-4 py-3 text-sm" required />
                            <input name="feedback" value="{{ $answer->feedback }}" placeholder="Optional feedback for the theory answer" class="rounded-2xl border border-slate-300 px-4 py-3 text-sm" />
                            <button type="submit" class="rounded-full bg-slate-900 px-4 py-3 text-sm font-semibold text-white">Save grade</button>
                        </form>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
</x-app-layout>
