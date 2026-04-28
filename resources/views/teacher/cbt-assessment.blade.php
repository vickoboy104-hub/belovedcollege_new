<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Teacher CBT builder</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $assessment->title }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $assessment->subject->name }} | {{ $assessment->schoolClass->name }} | {{ $assessment->type->label() }}</p>
            </div>
            <a href="{{ route('teacher.learning') }}" class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Back to teaching</a>
        </div>
    </x-slot>

    @if ($errors->has('questions'))
        <div class="mb-6 rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
            {{ $errors->first('questions') }}
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-[1fr,1.1fr]">
        <section class="section-card">
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Question count</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">{{ $assessment->cbtQuestions->count() }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Total score</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">{{ number_format((float) $assessment->total_score, 2) }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Duration</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">{{ $assessment->cbt_duration_minutes }} mins</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Admin status</div>
                    <div class="mt-3 text-lg font-semibold text-slate-950">{{ $assessment->cbt_is_active ? 'Active' : 'Inactive' }}</div>
                </div>
            </div>

            <div class="mt-6 rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 text-sm leading-7 text-slate-600">
                <div class="font-semibold text-slate-900">Instructions</div>
                <p class="mt-2 whitespace-pre-line">{{ $assessment->cbt_instructions ?: 'No special instructions added yet.' }}</p>
            </div>

            @if ($questionBankLocked)
                <div class="mt-6 rounded-[1.75rem] border border-amber-200 bg-amber-50 px-5 py-5 text-sm text-amber-900">
                    Students have already started this CBT. The question bank is now locked to protect submitted answers.
                </div>
            @endif
        </section>

        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Add CBT question</h2>
            @if ($questionBankLocked)
                <div class="mt-6 rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">
                    New questions cannot be added after students have started attempting this CBT.
                </div>
            @else
                <form method="POST" action="{{ route('teacher.cbt.questions.store', $assessment) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <select name="question_type" class="rounded-2xl border border-slate-300 px-4 py-3 text-sm" required>
                            <option value="objective">Objective / multiple choice</option>
                            <option value="theory">Theory question</option>
                        </select>
                        <input name="points" type="number" step="0.01" min="1" value="1" class="rounded-2xl border border-slate-300 px-4 py-3 text-sm" required />
                    </div>
                    <textarea name="prompt" rows="6" placeholder="Type the full question here" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" required></textarea>
                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Question images</span>
                            <input type="file" name="image_paths[]" accept="image/*" multiple class="block w-full text-sm" />
                        </label>
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                            <span class="mb-2 block font-semibold text-slate-900">Question video</span>
                            <input type="file" name="video_file" accept=".mp4,.webm,.mov,.m4v,video/mp4,video/webm,video/quicktime" class="block w-full text-sm" />
                        </label>
                        <div class="space-y-4">
                            <input name="video_url" placeholder="Or video URL" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" />
                            <input name="resource_link" placeholder="Supporting link" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="text-sm font-semibold text-slate-900">Options</div>
                        @foreach (range(0, 3) as $index)
                            <div class="grid gap-4 md:grid-cols-[1fr,140px]">
                                <input name="options[]" placeholder="Option {{ chr(65 + $index) }}" class="rounded-2xl border border-slate-300 px-4 py-3 text-sm" />
                                <label class="flex items-center gap-3 rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                                    <input type="radio" name="correct_option" value="{{ $index }}" class="rounded border-slate-300" @checked($index === 0) />
                                    Correct answer
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-3">
                        <div class="text-sm font-semibold text-slate-900">Theory marking guide</div>
                        <textarea name="theory_sample_answer" rows="4" placeholder="Optional sample answer or guide for teachers marking the theory question" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"></textarea>
                    </div>

                    <button type="submit" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white">Add question</button>
                </form>
            @endif
        </section>
    </div>

    <section class="section-card mt-8">
        <h2 class="display-font text-2xl font-bold text-slate-950">Question bank</h2>
        <div class="mt-5 space-y-4">
            @forelse ($assessment->cbtQuestions as $question)
                @php
                    $optionSlots = max(4, $question->options->count());
                    $optionTexts = $question->options->pluck('option_text')->pad($optionSlots, '');
                    $correctOptionIndex = $question->options->search(fn ($option) => $option->is_correct);
                @endphp
                <article class="rounded-3xl border border-slate-200 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex-1">
                            <div class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ ucfirst($question->question_type) }} | {{ number_format((float) $question->points, 2) }} mark(s)</div>
                            <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $question->prompt }}</p>
                            @if (filled($question->image_paths))
                                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                    @foreach ($question->image_paths as $image)
                                        <a href="{{ asset($image) }}" target="_blank" class="overflow-hidden rounded-2xl border border-slate-200">
                                            <img src="{{ asset($image) }}" alt="Question image" class="h-36 w-full object-cover" />
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
                                <div class="mt-4 space-y-2">
                                    @foreach ($question->options as $option)
                                        <div class="rounded-2xl border border-slate-200 px-4 py-3 text-sm {{ $option->is_correct ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'text-slate-700' }}">
                                            {{ $option->option_text }}
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($question->theory_sample_answer)
                                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-600">
                                    <div class="font-semibold text-slate-900">Sample answer / marking guide</div>
                                    <p class="mt-2 whitespace-pre-line">{{ $question->theory_sample_answer }}</p>
                                </div>
                            @endif

                            @if (! $questionBankLocked)
                                <details class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 px-5 py-5">
                                    <summary class="cursor-pointer text-sm font-semibold text-slate-900">Edit this question</summary>
                                    <form method="POST" action="{{ route('teacher.cbt.questions.update', $question) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                                        @csrf
                                        @method('PATCH')
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <select name="question_type" class="rounded-2xl border border-slate-300 px-4 py-3 text-sm" required>
                                                <option value="objective" @selected($question->question_type === 'objective')>Objective / multiple choice</option>
                                                <option value="theory" @selected($question->question_type === 'theory')>Theory question</option>
                                            </select>
                                            <input name="points" type="number" step="0.01" min="1" value="{{ $question->points }}" class="rounded-2xl border border-slate-300 px-4 py-3 text-sm" required />
                                        </div>
                                        <textarea name="prompt" rows="6" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" required>{{ $question->prompt }}</textarea>
                                        <div class="grid gap-4 md:grid-cols-3">
                                            <div class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                                                <span class="mb-2 block font-semibold text-slate-900">Add question images</span>
                                                <input type="file" name="image_paths[]" accept="image/*" multiple class="block w-full text-sm" />
                                                @if (filled($question->image_paths))
                                                    <label class="mt-3 flex items-center gap-3">
                                                        <input type="checkbox" name="remove_existing_images" value="1" class="rounded border-slate-300" />
                                                        Remove current images
                                                    </label>
                                                @endif
                                            </div>
                                            <div class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                                                <span class="mb-2 block font-semibold text-slate-900">Replace question video</span>
                                                <input type="file" name="video_file" accept=".mp4,.webm,.mov,.m4v,video/mp4,video/webm,video/quicktime" class="block w-full text-sm" />
                                                @if ($question->video_path)
                                                    <label class="mt-3 flex items-center gap-3">
                                                        <input type="checkbox" name="remove_video" value="1" class="rounded border-slate-300" />
                                                        Remove current video
                                                    </label>
                                                @endif
                                            </div>
                                            <div class="space-y-4">
                                                <input name="video_url" value="{{ $question->video_url }}" placeholder="Or video URL" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" />
                                                <input name="resource_link" value="{{ $question->resource_link }}" placeholder="Supporting link" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" />
                                            </div>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="text-sm font-semibold text-slate-900">Options</div>
                                            @foreach ($optionTexts as $index => $optionText)
                                                <div class="grid gap-4 md:grid-cols-[1fr,140px]">
                                                    <input name="options[]" value="{{ $optionText }}" placeholder="Option {{ chr(65 + $index) }}" class="rounded-2xl border border-slate-300 px-4 py-3 text-sm" />
                                                    <label class="flex items-center gap-3 rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                                                        <input type="radio" name="correct_option" value="{{ $index }}" class="rounded border-slate-300" @checked($correctOptionIndex !== false && (int) $correctOptionIndex === (int) $index) />
                                                        Correct answer
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="space-y-3">
                                            <div class="text-sm font-semibold text-slate-900">Theory marking guide</div>
                                            <textarea name="theory_sample_answer" rows="4" placeholder="Optional sample answer or guide for teachers marking the theory question" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">{{ $question->theory_sample_answer }}</textarea>
                                        </div>

                                        <button type="submit" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white">Save question changes</button>
                                    </form>
                                </details>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @if ($questionBankLocked)
                                <div class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Locked</div>
                            @else
                                <form method="POST" action="{{ route('teacher.cbt.questions.destroy', $question) }}" onsubmit="return confirm('Delete this CBT question?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No CBT questions added yet.</div>
            @endforelse
        </div>
    </section>

    <section class="section-card mt-8">
        <h2 class="display-font text-2xl font-bold text-slate-950">Student attempts</h2>
        <div class="mt-5 space-y-4">
            @forelse ($assessment->cbtAttempts as $attempt)
                <article class="rounded-3xl border border-slate-200 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $attempt->student->user->fullName() }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ ucfirst($attempt->status) }} | Objective {{ number_format((float) $attempt->objective_score, 2) }} | Total {{ number_format((float) $attempt->total_score, 2) }}</div>
                        </div>
                        <a href="{{ route('teacher.cbt.attempts.show', $attempt) }}" class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Review attempt</a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No student has attempted this CBT assessment yet.</div>
            @endforelse
        </div>
    </section>
</x-app-layout>
