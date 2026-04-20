<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Student CBT</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $assessment->title }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $assessment->subject->name }} | {{ $student->schoolClass->name ?? 'Class pending' }} | {{ $assessment->teacher->fullName() }}</p>
            </div>
            <div
                x-data="{ endsAt: new Date(@js(optional($attempt->expires_at)?->toIso8601String())).getTime(), remaining: '' }"
                x-init="
                    const tick = () => {
                        const diff = endsAt - Date.now();
                        if (diff <= 0) {
                            remaining = 'Time up';
                            return;
                        }
                        const totalSeconds = Math.floor(diff / 1000);
                        const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
                        const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
                        const seconds = String(totalSeconds % 60).padStart(2, '0');
                        remaining = `${hours}:${minutes}:${seconds}`;
                    };
                    tick();
                    setInterval(tick, 1000);
                "
                class="rounded-3xl brand-gradient px-6 py-5 text-white shadow-xl shadow-slate-900/10"
            >
                <div class="text-xs uppercase tracking-[0.3em] text-white/70">Time remaining</div>
                <div class="display-font mt-2 text-2xl font-bold" x-text="remaining"></div>
                <div class="mt-1 text-sm text-white/80">{{ $assessment->cbt_duration_minutes }} minutes</div>
            </div>
        </div>
    </x-slot>

    <section class="section-card">
        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 text-sm leading-7 text-slate-600">
            <div class="font-semibold text-slate-900">Instructions</div>
            <p class="mt-2 whitespace-pre-line">{{ $assessment->cbt_instructions ?: 'Answer all questions carefully and submit before the timer ends.' }}</p>
        </div>

        <form method="POST" action="{{ route('portal.cbt.submit', $assessment) }}" class="mt-8 space-y-6">
            @csrf
            @foreach ($assessment->cbtQuestions as $index => $question)
                <article class="rounded-3xl border border-slate-200 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Question {{ $index + 1 }} | {{ ucfirst($question->question_type) }} | {{ number_format((float) $question->points, 2) }} mark(s)</div>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $question->prompt }}</p>

                    @if (filled($question->image_paths))
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($question->image_paths as $image)
                                <a href="{{ asset($image) }}" target="_blank" class="overflow-hidden rounded-3xl border border-slate-200">
                                    <img src="{{ asset($image) }}" alt="Question image" class="h-56 w-full object-cover" />
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($question->video_path)
                        <video controls preload="metadata" class="mt-4 w-full rounded-3xl border border-slate-200 bg-slate-950">
                            <source src="{{ asset($question->video_path) }}">
                        </video>
                    @elseif ($question->video_url)
                        <a href="{{ $question->video_url }}" target="_blank" class="mt-4 inline-flex text-sm font-semibold text-slate-900">Open question video</a>
                    @endif

                    @if ($question->resource_link)
                        <div class="mt-3">
                            <a href="{{ $question->resource_link }}" target="_blank" class="text-sm font-semibold text-[color:var(--theme-primary)]">Open supporting link</a>
                        </div>
                    @endif

                    @if ($question->question_type === 'objective')
                        <div class="mt-5 space-y-3">
                            @foreach ($question->options as $option)
                                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 text-sm text-slate-700">
                                    <input type="radio" name="answers[{{ $question->id }}][option]" value="{{ $option->id }}" class="mt-1 rounded border-slate-300" />
                                    <span>{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <textarea name="answers[{{ $question->id }}][text]" rows="6" placeholder="Type your answer here" class="mt-5 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></textarea>
                    @endif
                </article>
            @endforeach

            <button type="submit" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white">Submit CBT exam</button>
        </form>
    </section>
</x-app-layout>
