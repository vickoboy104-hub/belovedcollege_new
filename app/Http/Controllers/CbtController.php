<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\CbtAnswer;
use App\Models\CbtAttempt;
use App\Models\CbtQuestion;
use App\Models\CbtQuestionOption;
use Illuminate\Support\Collection;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CbtController extends Controller
{
    public function storeAssessment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'term_id' => ['nullable', 'exists:terms,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:quiz,test,exam'],
            'cbt_duration_minutes' => ['required', 'integer', 'min:5', 'max:300'],
            'cbt_starts_at' => ['nullable', 'date'],
            'cbt_ends_at' => ['nullable', 'date', 'after:cbt_starts_at'],
            'cbt_instructions' => ['nullable', 'string', 'max:10000'],
            'cbt_show_results' => ['nullable', 'boolean'],
        ]);

        $this->ensureTeacherCanManageClass($request, (int) $validated['school_class_id']);

        $request->user()->assessments()->create([
            ...$validated,
            'is_cbt' => true,
            'cbt_is_active' => false,
            'cbt_show_results' => $request->boolean('cbt_show_results'),
            'total_score' => 0,
            'scheduled_at' => $validated['cbt_starts_at'] ?? null,
            'notes' => 'CBT assessment',
        ]);

        return redirect()->route('teacher.learning')->with('status', 'CBT assessment created. Add questions and wait for admin activation.');
    }

    public function showAssessment(Request $request, Assessment $assessment): View
    {
        $this->authorizeAssessmentAuthoring($request->user(), $assessment);

        $assessment->load([
            'subject',
            'schoolClass',
            'term',
            'cbtQuestions.options',
            'cbtAttempts.student.user',
        ]);

        return view('teacher.cbt-assessment', [
            'assessment' => $assessment,
            'questionBankLocked' => $assessment->cbtAttempts->isNotEmpty(),
        ]);
    }

    public function storeQuestion(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorizeAssessmentAuthoring($request->user(), $assessment);

        abort_unless($assessment->is_cbt, 404);

        if ($assessment->cbtAttempts()->exists()) {
            return back()->withErrors([
                'questions' => 'Questions cannot be changed after students have started this CBT.',
            ]);
        }

        [$validated, $options, $validationErrors] = $this->prepareQuestionPayload($request);

        if ($validationErrors !== []) {
            return back()->withErrors($validationErrors)->withInput();
        }

        $question = $assessment->cbtQuestions()->create([
            'question_type' => $validated['question_type'],
            'prompt' => $validated['prompt'],
            'points' => $validated['points'],
            'image_paths' => $request->hasFile('image_paths')
                ? $this->storeUploadedFiles($request->file('image_paths'), 'cbt/question-images', Str::slug($assessment->title).'-question')
                : [],
            'video_path' => $request->hasFile('video_file')
                ? $this->storeUploadedFile($request->file('video_file'), 'cbt/question-videos', Str::slug($assessment->title).'-question-video')
                : null,
            'video_url' => $validated['video_url'] ?? null,
            'resource_link' => $validated['resource_link'] ?? null,
            'theory_sample_answer' => $validated['question_type'] === 'theory' ? ($validated['theory_sample_answer'] ?? null) : null,
            'sort_order' => ((int) $assessment->cbtQuestions()->max('sort_order')) + 1,
        ]);

        if ($validated['question_type'] === 'objective') {
            $options->filter(fn (array $option) => $option['text'] !== '')->values()->each(function (array $option, int $index) use ($question, $validated): void {
                $question->options()->create([
                    'option_text' => $option['text'],
                    'is_correct' => $option['index'] === (int) $validated['correct_option'],
                    'sort_order' => $index + 1,
                ]);
            });
        }

        $assessment->refresh()->syncCbtTotalScore();

        return back()->with('status', 'CBT question added successfully.');
    }

    public function updateQuestion(Request $request, CbtQuestion $question): RedirectResponse
    {
        $question->load('assessment', 'options');
        $this->authorizeAssessmentAuthoring($request->user(), $question->assessment);

        abort_unless($question->assessment->is_cbt, 404);

        if ($question->assessment->cbtAttempts()->exists()) {
            return back()->withErrors([
                'questions' => 'Questions cannot be edited after students have started this CBT.',
            ]);
        }

        [$validated, $options, $validationErrors] = $this->prepareQuestionPayload($request);

        if ($validationErrors !== []) {
            return back()->withErrors($validationErrors)->withInput();
        }

        $imagePaths = collect($question->image_paths ?? []);

        if ($request->boolean('remove_existing_images')) {
            $imagePaths = collect();
        }

        if ($request->hasFile('image_paths')) {
            $imagePaths = $imagePaths->concat(
                $this->storeUploadedFiles($request->file('image_paths'), 'cbt/question-images', Str::slug($question->assessment->title).'-question')
            );
        }

        $videoPath = $request->boolean('remove_video') ? null : $question->video_path;

        if ($request->hasFile('video_file')) {
            $videoPath = $this->storeUploadedFile($request->file('video_file'), 'cbt/question-videos', Str::slug($question->assessment->title).'-question-video');
        }

        $question->update([
            'question_type' => $validated['question_type'],
            'prompt' => $validated['prompt'],
            'points' => $validated['points'],
            'image_paths' => $imagePaths->values()->all(),
            'video_path' => $videoPath,
            'video_url' => $validated['video_url'] ?? null,
            'resource_link' => $validated['resource_link'] ?? null,
            'theory_sample_answer' => $validated['question_type'] === 'theory' ? ($validated['theory_sample_answer'] ?? null) : null,
        ]);

        $question->options()->delete();

        if ($validated['question_type'] === 'objective') {
            $options->filter(fn (array $option) => $option['text'] !== '')->values()->each(function (array $option, int $index) use ($question, $validated): void {
                $question->options()->create([
                    'option_text' => $option['text'],
                    'is_correct' => $option['index'] === (int) $validated['correct_option'],
                    'sort_order' => $index + 1,
                ]);
            });
        }

        $question->assessment->refresh()->syncCbtTotalScore();

        return back()->with('status', 'CBT question updated successfully.');
    }

    public function destroyQuestion(Request $request, CbtQuestion $question): RedirectResponse
    {
        $question->load('assessment');
        $this->authorizeAssessmentAuthoring($request->user(), $question->assessment);

        if ($question->assessment->cbtAttempts()->exists()) {
            return back()->withErrors([
                'questions' => 'Questions cannot be deleted after students have started this CBT.',
            ]);
        }

        $assessment = $question->assessment;
        $question->delete();
        $assessment->refresh()->syncCbtTotalScore();

        return back()->with('status', 'CBT question deleted.');
    }

    public function showAttemptReview(Request $request, CbtAttempt $attempt): View
    {
        $attempt->load([
            'assessment.subject',
            'assessment.schoolClass',
            'student.user',
            'answers.question.options',
            'answers.selectedOption',
        ]);

        $this->authorizeAssessmentAuthoring($request->user(), $attempt->assessment);

        return view('teacher.cbt-attempt', [
            'attempt' => $attempt,
        ]);
    }

    public function gradeAnswer(Request $request, CbtAnswer $answer): RedirectResponse
    {
        $answer->load('attempt.assessment', 'question');
        $this->authorizeAssessmentAuthoring($request->user(), $answer->attempt->assessment);

        abort_unless($answer->question->question_type === 'theory', 404);

        $validated = $request->validate([
            'awarded_score' => ['required', 'numeric', 'min:0', 'max:'.$answer->question->points],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $answer->update([
            'awarded_score' => $validated['awarded_score'],
            'feedback' => $validated['feedback'] ?? null,
            'graded_at' => now(),
        ]);

        $answer->attempt->update([
            'graded_by' => $request->user()->id,
        ]);
        $answer->attempt->refresh()->syncScores();

        return back()->with('status', 'Theory answer graded successfully.');
    }

    public function takeAssessment(Request $request, Assessment $assessment): View|RedirectResponse
    {
        $student = $request->user()->studentProfile()->with('schoolClass')->first();

        abort_unless($student, 403);
        abort_unless($assessment->is_cbt, 404);
        abort_unless((string) Setting::getValue('cbt_enabled', '1') === '1', 403);
        abort_unless($assessment->school_class_id === $student->school_class_id, 403);
        abort_unless($assessment->cbt_is_active, 403);

        if ($assessment->cbt_starts_at && now()->lt($assessment->cbt_starts_at)) {
            return redirect()->route('portal.index')->with('status', 'This CBT exam has not started yet.');
        }

        if ($assessment->cbt_ends_at && now()->gt($assessment->cbt_ends_at)) {
            return redirect()->route('portal.index')->with('status', 'This CBT exam is no longer available.');
        }

        $attempt = CbtAttempt::firstOrCreate(
            [
                'assessment_id' => $assessment->id,
                'student_id' => $student->id,
            ],
            [
                'status' => 'in_progress',
                'started_at' => now(),
                'expires_at' => now()->addMinutes($assessment->cbt_duration_minutes ?: 60),
            ],
        );

        if (in_array($attempt->status, ['submitted', 'graded'], true)) {
            return redirect()->route('portal.index')->with('status', 'You have already submitted this CBT exam.');
        }

        if ($attempt->expires_at && now()->gt($attempt->expires_at)) {
            return redirect()->route('portal.index')->with('status', 'This CBT exam time has expired.');
        }

        $assessment->load('subject', 'teacher', 'cbtQuestions.options');

        return view('portal.cbt-exam', [
            'assessment' => $assessment,
            'attempt' => $attempt,
            'student' => $student,
        ]);
    }

    public function submitAssessment(Request $request, Assessment $assessment): RedirectResponse
    {
        $student = $request->user()->studentProfile()->first();

        abort_unless($student, 403);
        abort_unless($assessment->is_cbt, 404);

        $attempt = CbtAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('portal.index')->with('status', 'This CBT exam has already been submitted.');
        }

        $assessment->load('cbtQuestions.options');
        $payload = $request->input('answers', []);

        foreach ($assessment->cbtQuestions as $question) {
            $answerPayload = data_get($payload, $question->id, []);

            if ($question->question_type === 'objective') {
                $selectedOptionId = (int) data_get($answerPayload, 'option');
                $selectedOption = $question->options->firstWhere('id', $selectedOptionId);
                $isCorrect = $selectedOption?->is_correct ?? false;

                CbtAnswer::updateOrCreate(
                    [
                        'cbt_attempt_id' => $attempt->id,
                        'cbt_question_id' => $question->id,
                    ],
                    [
                        'selected_option_id' => $selectedOption?->id,
                        'answer_text' => null,
                        'is_correct' => $selectedOption ? $isCorrect : null,
                        'awarded_score' => $selectedOption && $isCorrect ? $question->points : 0,
                        'feedback' => null,
                        'graded_at' => now(),
                    ],
                );

                continue;
            }

            CbtAnswer::updateOrCreate(
                [
                    'cbt_attempt_id' => $attempt->id,
                    'cbt_question_id' => $question->id,
                ],
                [
                    'selected_option_id' => null,
                    'answer_text' => trim((string) data_get($answerPayload, 'text')),
                    'is_correct' => null,
                    'awarded_score' => null,
                    'feedback' => null,
                    'graded_at' => null,
                ],
            );
        }

        $attempt->update([
            'submitted_at' => now(),
        ]);
        $attempt->refresh()->syncScores();

        return redirect()->route('portal.index')->with('status', 'CBT exam submitted successfully.');
    }

    public function toggleGlobal(Request $request): RedirectResponse
    {
        Setting::setMany([
            'cbt_enabled' => $request->boolean('enabled') ? '1' : '0',
        ], 'school');

        return back()->with('status', $request->boolean('enabled') ? 'School CBT is now enabled.' : 'School CBT is now disabled.');
    }

    public function toggleAssessment(Request $request, Assessment $assessment): RedirectResponse
    {
        abort_unless($assessment->is_cbt, 404);

        $assessment->update([
            'cbt_is_active' => ! $assessment->cbt_is_active,
        ]);

        return back()->with('status', $assessment->cbt_is_active ? 'CBT exam activated.' : 'CBT exam deactivated.');
    }

    protected function authorizeAssessmentAuthoring($user, Assessment $assessment): void
    {
        if ($user->hasAnyRole(['admin', 'principal'])) {
            return;
        }

        if ($assessment->teacher_id === $user->id) {
            return;
        }

        abort_unless(
            $user->managedClasses()->whereKey($assessment->school_class_id)->exists(),
            403
        );
    }

    protected function ensureTeacherCanManageClass(Request $request, int $schoolClassId): void
    {
        $allowedClassIds = $this->managedClassIds($request);

        if ($allowedClassIds === null) {
            return;
        }

        abort_unless($allowedClassIds->contains($schoolClassId), 403);
    }

    protected function managedClassIds(Request $request): ?Collection
    {
        $user = $request->user();

        if ($user->hasAnyRole(['admin', 'principal'])) {
            return null;
        }

        $managedClassIds = $user->managedClasses()->pluck('school_classes.id');

        return $managedClassIds->isNotEmpty() ? $managedClassIds : null;
    }

    protected function prepareQuestionPayload(Request $request): array
    {
        $validated = $request->validate([
            'question_type' => ['required', 'in:objective,theory'],
            'prompt' => ['required', 'string', 'max:20000'],
            'points' => ['required', 'numeric', 'min:1', 'max:100'],
            'image_paths' => ['nullable', 'array'],
            'image_paths.*' => ['image', 'max:10240'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,mov,m4v', 'max:102400'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'resource_link' => ['nullable', 'url', 'max:500'],
            'theory_sample_answer' => ['nullable', 'string', 'max:10000'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:2000'],
            'correct_option' => ['nullable', 'integer', 'min:0', 'max:7'],
            'remove_existing_images' => ['nullable', 'boolean'],
            'remove_video' => ['nullable', 'boolean'],
        ]);

        $options = collect($validated['options'] ?? [])
            ->map(fn ($option, $index) => [
                'text' => trim((string) $option),
                'index' => $index,
            ])
            ->values();

        $errors = [];

        if ($validated['question_type'] === 'objective') {
            if ($options->filter(fn (array $option) => $option['text'] !== '')->count() < 2) {
                $errors['options'] = 'Objective questions need at least two options.';
            }

            if (! $options->has((int) $validated['correct_option']) || $options[(int) $validated['correct_option']]['text'] === '') {
                $errors['correct_option'] = 'Select the correct option for the objective question.';
            }
        }

        return [$validated, $options, $errors];
    }

    protected function storeUploadedFiles(array $files, string $directory, string $prefix): array
    {
        return collect($files)
            ->filter()
            ->values()
            ->map(fn ($file, $index) => $this->storeUploadedFile($file, $directory, $prefix.'-'.($index + 1)))
            ->all();
    }

    protected function storeUploadedFile($file, string $directory, string $prefix): string
    {
        $destination = public_path('uploads/'.$directory);

        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $filename = Str::slug($prefix).'-'.time().'-'.Str::lower(Str::random(4)).'.'.$file->getClientOriginalExtension();

        $file->move($destination, $filename);

        return 'uploads/'.$directory.'/'.$filename;
    }
}
