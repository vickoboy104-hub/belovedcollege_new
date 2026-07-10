<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$assessment->title" eyebrow="Student CBT Portal">
            <x-slot name="description">
                {{ $assessment->subject->name }} &bull; {{ $student->schoolClass->name ?? 'Class pending assignment' }} &bull; {{ $assessment->teacher->fullName() }}
            </x-slot>
            <x-slot name="actions">
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
                    class="rounded-[18px] bg-gradient-to-r from-[#071833] to-[#0b1f3a] px-5 py-3 text-white border border-[#c8d6ea]/20 shadow-md min-w-[180px] text-center"
                >
                    <div class="text-[9px] font-extrabold uppercase tracking-[0.2em] text-slate-350">Time Remaining</div>
                    <div class="display-font mt-1 text-xl font-black text-[#fbbf24] tracking-tight" x-text="remaining"></div>
                    <div class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $assessment->cbt_duration_minutes }} Mins Duration</div>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="space-y-8">
        <!-- Guidelines Card -->
        <x-dashboard-card title="Exam Guidelines & Instructions" icon="file-text" accent="gray">
            <p class="whitespace-pre-line text-sm text-slate-650 leading-relaxed font-semibold">
                {{ $assessment->cbt_instructions ?: 'Answer all questions carefully and submit before the countdown timer finishes.' }}
            </p>
        </x-dashboard-card>

        <!-- Exam Questions Form -->
        <form method="POST" action="{{ route('portal.cbt.submit', $assessment) }}" class="space-y-6">
            @csrf
            @foreach ($assessment->cbtQuestions as $index => $question)
                <article class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-sm space-y-4 hover:border-[#fbbf24] transition-all">
                    <!-- Question Header Info -->
                    <div class="flex items-center justify-between gap-3 flex-wrap border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#071833] text-[10px] font-black text-white shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <x-status-badge :status="$question->question_type === 'objective' ? 'open' : 'marked'" :label="ucfirst($question->question_type)" />
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-100 border border-slate-250 rounded-full px-2.5 py-0.5">
                            {{ number_format((float) $question->points, 2) }} Mark(s)
                        </span>
                    </div>

                    <!-- Prompt -->
                    <p class="whitespace-pre-line text-sm font-semibold text-slate-800 leading-relaxed">
                        {{ $question->prompt }}
                    </p>

                    <!-- Images -->
                    @if (filled($question->image_paths))
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($question->image_paths as $image)
                                <a href="{{ asset($image) }}" target="_blank" class="overflow-hidden rounded-xl border border-slate-200 block shadow-sm hover:opacity-95 transition">
                                    <img src="{{ asset($image) }}" alt="Question illustration" class="h-48 w-full object-cover" />
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <!-- Videos -->
                    @if ($question->video_path)
                        <div class="max-w-md">
                            <video controls preload="metadata" class="w-full rounded-xl border border-slate-300 bg-slate-950 shadow">
                                <source src="{{ asset($question->video_path) }}">
                            </video>
                        </div>
                    @elseif ($question->video_url)
                        <div>
                            <a href="{{ $question->video_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 transition">
                                <x-app-icon name="video" class="h-4 w-4 text-blue-500" />
                                <span>Open Video Resource</span>
                            </a>
                        </div>
                    @endif

                    <!-- Supporting Link -->
                    @if ($question->resource_link)
                        <div class="pt-2">
                            <a href="{{ $question->resource_link }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 transition">
                                <x-app-icon name="link" class="h-4 w-4 text-blue-500" />
                                <span>Open Supporting Resource Link</span>
                            </a>
                        </div>
                    @endif

                    <!-- Answer Options -->
                    @if ($question->question_type === 'objective')
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($question->options as $idx => $option)
                                <label class="flex items-start gap-2.5 rounded-xl border border-slate-200 px-4 py-3 text-xs text-slate-700 bg-slate-50/30 hover:bg-slate-50 hover:border-[#fbbf24] cursor-pointer select-none transition shadow-sm">
                                    <input type="radio" name="answers[{{ $question->id }}][option]" value="{{ $option->id }}" class="mt-0.5 rounded text-blue-600 border-slate-350 focus:ring-blue-500" />
                                    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-black bg-slate-100 text-slate-500">
                                        {{ chr(65 + $idx) }}
                                    </span>
                                    <span class="flex-1 leading-6 font-semibold">{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wide">Type your response below</label>
                            <textarea name="answers[{{ $question->id }}][text]" rows="5" placeholder="Write your complete solution or answer notes here..." class="theme-input w-full text-xs font-semibold"></textarea>
                        </div>
                    @endif
                </article>
            @endforeach

            <!-- Action buttons -->
            <div class="flex items-center justify-end pt-4">
                <x-action-button type="submit" variant="accent" icon="save">
                    Submit CBT Exam
                </x-action-button>
            </div>
        </form>
    </div>
</x-app-layout>
