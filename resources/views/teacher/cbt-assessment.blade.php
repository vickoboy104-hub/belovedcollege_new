<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$assessment->title" eyebrow="Teacher CBT Builder">
            <x-slot name="description">
                {{ $assessment->subject->name }} &bull; {{ $assessment->schoolClass->name }} &bull; {{ $assessment->type->label() }}
            </x-slot>
            <x-slot name="actions">
                <x-action-button :href="route('teacher.learning', ['section' => 'cbt-list'])" variant="secondary" icon="arrow-left">
                    Back to teaching
                </x-action-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if (isset($errors) && $errors->has('questions'))
        <div class="mb-6 rounded-[18px] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800 shadow-sm flex items-center gap-3">
            <x-app-icon name="exclamation-circle" class="h-5 w-5 shrink-0 text-rose-600" />
            <span>{{ $errors->first('questions') }}</span>
        </div>
    @endif

    <div class="space-y-8">
        <!-- Stats and Instructions Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Question Count" :value="$assessment->cbtQuestions->count()" icon="book" accent="blue" />
            <x-stat-card label="Total Score" :value="number_format((float) $assessment->total_score, 2)" icon="award" accent="green" />
            <x-stat-card label="Duration" :value="$assessment->cbt_duration_minutes . ' Mins'" icon="clock" accent="purple" />
            <x-stat-card label="Admin Status" :value="$assessment->cbt_is_active ? 'Active' : 'Inactive'" :icon="$assessment->cbt_is_active ? 'check-circle' : 'x-circle'" :accent="$assessment->cbt_is_active ? 'green' : 'red'" />
        </div>

        <div class="grid gap-8 xl:grid-cols-[1fr,1.2fr]">
            <!-- Instructions and Lock State -->
            <div class="space-y-6">
                <x-dashboard-card title="CBT Instructions" icon="file-text" accent="gray">
                    <p class="whitespace-pre-line text-sm text-slate-600 leading-relaxed">
                        {{ $assessment->cbt_instructions ?: 'No special instructions added yet.' }}
                    </p>

                    @if ($questionBankLocked)
                        <div class="mt-6 rounded-[14px] border border-amber-200 bg-amber-50 p-4.5 text-xs font-semibold text-amber-900 leading-relaxed flex items-start gap-3">
                            <x-app-icon name="lock" class="h-5 w-5 shrink-0 text-amber-600" />
                            <div>
                                <span class="font-bold block text-amber-950 mb-0.5">Question Bank Locked</span>
                                Students have already started taking this CBT exam. The questions are now locked to preserve attempt history and prevent grading issues.
                            </div>
                        </div>
                    @endif
                </x-dashboard-card>
            </div>

            <!-- Add Question Form -->
            <div>
                @if ($questionBankLocked)
                    <x-dashboard-card title="Add Question" icon="plus" accent="gray">
                        <div class="rounded-[14px] border border-dashed border-slate-300 py-8 px-4 text-center text-sm text-slate-500">
                            <x-app-icon name="lock" class="h-8 w-8 mx-auto text-slate-400 mb-2" />
                            <p class="font-bold text-slate-700">Question Addition Disabled</p>
                            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">New questions cannot be added because students have already begun attempting this cbt exam.</p>
                        </div>
                    </x-dashboard-card>
                @else
                    <x-form-card :action="route('teacher.cbt.questions.store', $assessment)" method="POST" title="Add CBT Question" description="Create a new question, set the point weight, and specify the correct answer." enctype="multipart/form-data">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Question Type</label>
                                <select name="question_type" class="theme-input w-full" required>
                                    <option value="objective">Objective / Multiple Choice</option>
                                    <option value="theory">Theory / Free Text</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Points Weight</label>
                                <input name="points" type="number" step="0.01" min="0.1" value="1" class="theme-input w-full" required />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Question Prompt / Text</label>
                            <textarea name="prompt" rows="4" placeholder="Type the question prompt here..." class="theme-input w-full" required></textarea>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3 bg-slate-50 p-4 rounded-[14px] border border-slate-100">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide cursor-pointer">
                                <span class="mb-1.5 block font-bold text-slate-800">Question Images</span>
                                <input type="file" name="image_paths[]" accept="image/*" multiple class="block w-full text-xs font-semibold text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            </label>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide cursor-pointer">
                                <span class="mb-1.5 block font-bold text-slate-800">Upload Video File</span>
                                <input type="file" name="video_file" accept=".mp4,.webm,.mov,.m4v,video/mp4,video/webm,video/quicktime" class="block w-full text-xs font-semibold text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            </label>
                            <div class="space-y-2">
                                <input name="video_url" placeholder="Or paste Video URL" class="theme-input w-full text-xs py-2" />
                                <input name="resource_link" placeholder="Supporting Link" class="theme-input w-full text-xs py-2" />
                            </div>
                        </div>

                        <div class="space-y-4 border-t border-slate-100 pt-4">
                            <div class="text-xs font-bold text-slate-700 uppercase tracking-wide">Multiple Choice Options (For Objective)</div>
                            @foreach (range(0, 3) as $index)
                                <div class="grid gap-3 md:grid-cols-[1fr,160px]">
                                    <input name="options[]" placeholder="Option {{ chr(65 + $index) }}" class="theme-input w-full" />
                                    <label class="flex items-center gap-2 rounded-[12px] border border-slate-300 px-3 py-2 text-xs text-slate-600 bg-white cursor-pointer hover:bg-slate-50 select-none">
                                        <input type="radio" name="correct_option" value="{{ $index }}" class="rounded text-blue-600 border-slate-300 focus:ring-blue-500" @checked($index === 0) />
                                        <span class="font-bold">Correct Option</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-2 border-t border-slate-100 pt-4">
                            <div class="text-xs font-bold text-slate-700 uppercase tracking-wide">Theory Marking Guide / Sample Answer</div>
                            <textarea name="theory_sample_answer" rows="3" placeholder="Optional marking guide or correct key phrases to look for when grading..." class="theme-input w-full"></textarea>
                        </div>

                        <x-slot name="actions">
                            <x-action-button type="submit" variant="primary" icon="plus">Add Question</x-action-button>
                        </x-slot>
                    </x-form-card>
                @endif
            </div>
        </div>

        <!-- Question Bank Section -->
        <x-dashboard-card title="Question Bank Library" subtitle="Review and edit all the questions assigned to this CBT exam." icon="book-open" accent="blue">
            <div class="space-y-6">
                @forelse ($assessment->cbtQuestions as $question)
                    @php
                        $optionSlots = max(4, $question->options->count());
                        $optionTexts = $question->options->pluck('option_text')->pad($optionSlots, '');
                        $correctOptionIndex = $question->options->search(fn ($option) => $option->is_correct);
                    @endphp
                    <article class="rounded-[18px] border border-slate-200 bg-slate-50/50 p-5 shadow-sm hover:border-[#fbbf24] transition-all">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex-1 space-y-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <x-status-badge :status="$question->question_type === 'objective' ? 'open' : 'marked'" :label="ucfirst($question->question_type)" />
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-100 border border-slate-200 rounded-full px-2.5 py-0.5">
                                        {{ number_format((float) $question->points, 2) }} Mark(s)
                                    </span>
                                </div>

                                <p class="whitespace-pre-line text-sm font-semibold text-slate-800 leading-relaxed">{{ $question->prompt }}</p>

                                @if (filled($question->image_paths))
                                    <div class="grid gap-3 sm:grid-cols-4">
                                        @foreach ($question->image_paths as $image)
                                            <a href="{{ asset($image) }}" target="_blank" class="overflow-hidden rounded-xl border border-slate-200 block shadow-sm hover:opacity-90 transition">
                                                <img src="{{ asset($image) }}" alt="Question visual aid" class="h-28 w-full object-cover" />
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($question->video_path)
                                    <div class="max-w-md">
                                        <video controls preload="metadata" class="w-full rounded-xl border border-slate-300 bg-slate-950 shadow">
                                            <source src="{{ asset($question->video_path) }}">
                                        </video>
                                    </div>
                                @elseif ($question->video_url)
                                    <a href="{{ $question->video_url }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:text-blue-700 bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm">
                                        <x-app-icon name="video" class="h-4 w-4 text-blue-500" />
                                        <span>Watch Question Video Resource</span>
                                    </a>
                                @endif

                                @if ($question->resource_link)
                                    <div>
                                        <a href="{{ $question->resource_link }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:text-blue-700 bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm">
                                            <x-app-icon name="link" class="h-4 w-4 text-blue-500" />
                                            <span>Open Supporting Link</span>
                                        </a>
                                    </div>
                                @endif

                                @if ($question->question_type === 'objective')
                                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                        @foreach ($question->options as $index => $option)
                                            <div class="rounded-xl border p-3 text-xs leading-relaxed flex items-start gap-2.5 shadow-sm {{ $option->is_correct ? 'border-emerald-200 bg-emerald-50 text-emerald-950 font-bold' : 'border-slate-200 bg-white text-slate-700' }}">
                                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-black {{ $option->is_correct ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500' }}">
                                                    {{ chr(65 + $index) }}
                                                </span>
                                                <span class="flex-1">{{ $option->option_text }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif ($question->theory_sample_answer)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs leading-relaxed text-slate-600">
                                        <div class="font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                                            <x-app-icon name="file-text" class="h-4 w-4 text-slate-500" />
                                            <span>Sample Answer / Grading Key:</span>
                                        </div>
                                        <p class="whitespace-pre-line">{{ $question->theory_sample_answer }}</p>
                                    </div>
                                @endif

                                @if (! $questionBankLocked)
                                    <div class="pt-4">
                                        <details class="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                            <summary class="cursor-pointer text-xs font-bold text-slate-700 uppercase tracking-wider hover:text-slate-900 select-none flex items-center justify-between">
                                                <span>Edit Question Details</span>
                                                <x-app-icon name="chevron-down" class="h-4 w-4 transform group-open:rotate-180 transition-transform duration-200" />
                                            </summary>
                                            <form method="POST" action="{{ route('teacher.cbt.questions.update', $question) }}" enctype="multipart/form-data" class="mt-5 space-y-4 border-t border-slate-100 pt-4">
                                                @csrf
                                                @method('PATCH')
                                                
                                                <div class="grid gap-4 md:grid-cols-2">
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wide mb-1">Question Type</label>
                                                        <select name="question_type" class="theme-input w-full text-xs py-2" required>
                                                            <option value="objective" @selected($question->question_type === 'objective')>Objective / Multiple Choice</option>
                                                            <option value="theory" @selected($question->question_type === 'theory')>Theory / Free Text</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wide mb-1">Points Weight</label>
                                                        <input name="points" type="number" step="0.01" min="0.1" value="{{ $question->points }}" class="theme-input w-full text-xs py-2" required />
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wide mb-1">Question Prompt / Text</label>
                                                    <textarea name="prompt" rows="4" class="theme-input w-full text-xs" required>{{ $question->prompt }}</textarea>
                                                </div>

                                                <div class="grid gap-4 md:grid-cols-3 bg-slate-50 p-3.5 rounded-[12px] border border-slate-100">
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wide mb-1">Add Question Images</label>
                                                        <input type="file" name="image_paths[]" accept="image/*" multiple class="block w-full text-[10px] font-semibold text-slate-500" />
                                                        @if (filled($question->image_paths))
                                                            <label class="mt-2 flex items-center gap-2 cursor-pointer select-none">
                                                                <input type="checkbox" name="remove_existing_images" value="1" class="rounded text-blue-600 border-slate-300" />
                                                                <span class="text-[10px] font-bold text-rose-600 uppercase">Remove current images</span>
                                                            </label>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wide mb-1">Replace Video File</label>
                                                        <input type="file" name="video_file" accept=".mp4,.webm,.mov,.m4v,video/mp4,video/webm,video/quicktime" class="block w-full text-[10px] font-semibold text-slate-500" />
                                                        @if ($question->video_path)
                                                            <label class="mt-2 flex items-center gap-2 cursor-pointer select-none">
                                                                <input type="checkbox" name="remove_video" value="1" class="rounded text-blue-600 border-slate-300" />
                                                                <span class="text-[10px] font-bold text-rose-600 uppercase">Remove current video</span>
                                                            </label>
                                                        @endif
                                                    </div>
                                                    <div class="space-y-2">
                                                        <input name="video_url" value="{{ $question->video_url }}" placeholder="Or video URL" class="theme-input w-full text-[10px] py-1.5" />
                                                        <input name="resource_link" value="{{ $question->resource_link }}" placeholder="Supporting link" class="theme-input w-full text-[10px] py-1.5" />
                                                    </div>
                                                </div>

                                                <div class="space-y-3 border-t border-slate-100 pt-3">
                                                    <div class="text-[10px] font-bold text-slate-700 uppercase tracking-wide">Options</div>
                                                    @foreach ($optionTexts as $idx => $optionText)
                                                        <div class="grid gap-3 md:grid-cols-[1fr,150px]">
                                                            <input name="options[]" value="{{ $optionText }}" placeholder="Option {{ chr(65 + $idx) }}" class="theme-input w-full text-xs py-1.5" />
                                                            <label class="flex items-center gap-2 rounded-[10px] border border-slate-300 px-3 py-1.5 text-[10px] text-slate-600 bg-white cursor-pointer hover:bg-slate-50 select-none">
                                                                <input type="radio" name="correct_option" value="{{ $idx }}" class="rounded text-blue-600 border-slate-300" @checked($correctOptionIndex !== false && (int) $correctOptionIndex === (int) $idx) />
                                                                <span class="font-bold">Correct Option</span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="space-y-1.5 border-t border-slate-100 pt-3">
                                                    <div class="text-[10px] font-bold text-slate-700 uppercase tracking-wide">Theory Marking Guide</div>
                                                    <textarea name="theory_sample_answer" rows="3" placeholder="Optional grading guide..." class="theme-input w-full text-xs">{{ $question->theory_sample_answer }}</textarea>
                                                </div>

                                                <div class="pt-2 flex justify-end">
                                                    <x-action-button type="submit" variant="accent" icon="save">Save Question Changes</x-action-button>
                                                </div>
                                            </form>
                                        </details>
                                    </div>
                                @endif
                            </div>

                            <div class="flex shrink-0 gap-2 self-start lg:self-auto">
                                @if ($questionBankLocked)
                                    <x-status-badge status="closed" label="Locked" />
                                @else
                                    <form method="POST" action="{{ route('teacher.cbt.questions.destroy', $question) }}" onsubmit="return confirm('Delete this CBT question?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-action-button type="submit" variant="danger" icon="trash" class="!py-1.5 !px-3">
                                            Delete
                                        </x-action-button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <x-empty-state title="No CBT questions added yet" subtitle="Use the 'Add CBT Question' panel to begin building your question bank for this assessment." icon="book-open" />
                @endforelse
            </div>
        </x-dashboard-card>

        <!-- Student Attempts Section -->
        <x-dashboard-card title="Student Attempts Tracker" subtitle="Monitor dynamic exam logs and grade theory attempts." icon="user-check" accent="green">
            <div class="space-y-4">
                @forelse ($assessment->cbtAttempts as $attempt)
                    <article class="rounded-[18px] border border-slate-200 p-4.5 bg-white shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:border-[#fbbf24] transition-all">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200">
                                <x-app-icon name="user" class="h-5 w-5 text-slate-500" />
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm leading-snug">{{ $attempt->student->user->fullName() }}</h4>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    <x-status-badge :status="$attempt->status" />
                                    <span class="text-xs text-slate-500">
                                        Objective: <span class="font-bold text-slate-700">{{ number_format((float) $attempt->objective_score, 2) }}</span>
                                    </span>
                                    <span class="text-xs text-slate-500">&bull;</span>
                                    <span class="text-xs text-slate-500">
                                        Total Score: <span class="font-bold text-slate-800">{{ number_format((float) $attempt->total_score, 2) }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <x-action-button :href="route('teacher.cbt.attempts.show', $attempt)" variant="secondary" icon="eye" class="!py-1.5 !px-3 shrink-0">
                            Review Attempt
                        </x-action-button>
                    </article>
                @empty
                    <x-empty-state title="No student attempts logged yet" subtitle="When student logins begin taking exams, their logs, durations, scores, and submissions will record here." icon="clock" />
                @endforelse
            </div>
        </x-dashboard-card>
    </div>
</x-app-layout>
