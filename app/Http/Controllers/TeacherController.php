<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\AttendanceRecord;
use App\Models\CbtAttempt;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Services\TeacherAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function learning(Request $request, TeacherAccessService $teacherAccess, ?string $section = null): View
    {
        $teacher = $request->user();
        $sections = collect([
            'publish-lesson',
            'create-assignment',
            'assessment',
            'record-result',
            'attendance',
            'cbt-create',
            'cbt-list',
            'latest-content',
            'submissions',
            'cbt-attempts',
        ]);
        $activeTeachingSection = $sections->contains($section) ? $section : 'publish-lesson';
        $privileged = $teacherAccess->isPrivileged($teacher);
        $teachingAssignments = $teacherAccess->activeAssignments($teacher);
        $classIds = $teacherAccess->classIds($teacher);
        $subjectIds = $teacherAccess->subjectIds($teacher);

        $classes = SchoolClass::query()
            ->when(! $privileged, fn (Builder $query) => $query->whereIn('id', $classIds ?? collect()))
            ->orderBy('name')
            ->orderBy('section')
            ->get();
        $subjects = Subject::query()
            ->when(! $privileged, fn (Builder $query) => $query->whereIn('id', $subjectIds ?? collect()))
            ->orderBy('name')
            ->get();
        $classSubjectMap = $privileged
            ? $classes->mapWithKeys(fn (SchoolClass $schoolClass) => [$schoolClass->id => $subjects->pluck('id')->all()])->all()
            : $teacherAccess->classSubjectMap($teacher);
        $managedClasses = $classes;
        $classTeacherMode = ! $privileged;
        $hasTeachingAccess = $privileged || $teachingAssignments->isNotEmpty();
        $terms = Term::with('academicSession')->latest()->get();
        $students = Student::with('user', 'schoolClass')
            ->when(! $privileged, fn (Builder $query) => $query->whereIn('school_class_id', $classIds ?? collect()))
            ->orderBy('admission_no')
            ->get();

        $lessonsQuery = Lesson::query()->with('subject', 'schoolClass', 'teacher');
        $teacherAccess->scopePairs($lessonsQuery, $teacher);
        $lessons = $lessonsQuery->latest()->take(10)->get();

        $assignmentsQuery = Assignment::query()->with('subject', 'schoolClass', 'teacher');
        $teacherAccess->scopePairs($assignmentsQuery, $teacher);
        $assignments = $assignmentsQuery->latest()->take(10)->get();

        $assessmentsQuery = Assessment::query()->with('subject', 'schoolClass', 'term', 'teacher');
        $teacherAccess->scopePairs($assessmentsQuery, $teacher);
        $assessments = $assessmentsQuery->latest()->take(20)->get();

        $cbtAssessmentsQuery = Assessment::query()
            ->where('is_cbt', true)
            ->with('subject', 'schoolClass', 'term', 'teacher')
            ->withCount('cbtQuestions', 'cbtAttempts');
        $teacherAccess->scopePairs($cbtAssessmentsQuery, $teacher);
        $cbtAssessments = $cbtAssessmentsQuery
            ->latest('cbt_starts_at')
            ->take(8)
            ->get();

        $cbtAttemptsNeedingReview = CbtAttempt::query()
            ->whereHas('assessment', function (Builder $query) use ($teacherAccess, $teacher): void {
                $query->where('is_cbt', true);
                $teacherAccess->scopePairs($query, $teacher);
            })
            ->with('assessment.subject', 'assessment.schoolClass', 'student.user')
            ->whereIn('status', ['submitted', 'graded'])
            ->latest('submitted_at')
            ->take(8)
            ->get();

        $submissions = AssignmentSubmission::query()
            ->whereHas('assignment', fn (Builder $query) => $teacherAccess->scopePairs($query, $teacher))
            ->with('assignment.teacher', 'assignment.subject', 'student.user', 'student.schoolClass')
            ->latest()
            ->take(10)
            ->get();

        return view('teacher.learning', compact(
            'classes',
            'subjects',
            'terms',
            'students',
            'lessons',
            'assignments',
            'assessments',
            'cbtAssessments',
            'cbtAttemptsNeedingReview',
            'submissions',
            'activeTeachingSection',
            'managedClasses',
            'classTeacherMode',
            'teachingAssignments',
            'classSubjectMap',
            'hasTeachingAccess',
        ));
    }

    public function storeLesson(Request $request, TeacherAccessService $teacherAccess): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:10000'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,mov,m4v', 'max:102400'],
            'resource_link' => ['nullable', 'url', 'max:500'],
            'note_images' => ['nullable', 'array'],
            'note_images.*' => ['image', 'max:10240'],
        ]);

        $teacherAccess->authorizePair($request->user(), (int) $validated['school_class_id'], (int) $validated['subject_id']);

        $noteImages = $request->hasFile('note_images')
            ? $this->storeUploadedFiles($request->file('note_images'), 'teaching/lesson-images', Str::slug($validated['title']).'-note')
            : [];

        $request->user()->lessons()->create([
            ...collect($validated)->except(['video_file', 'note_images'])->all(),
            'video_path' => $request->hasFile('video_file')
                ? $this->storeUploadedFile($request->file('video_file'), 'teaching/lesson-videos', Str::slug($validated['title']).'-video')
                : null,
            'note_images' => $noteImages,
            'published_at' => now(),
        ]);

        return back()->with('status', 'Lesson published successfully.');
    }

    public function storeAssignment(Request $request, TeacherAccessService $teacherAccess): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string', 'max:10000'],
            'attachment_images' => ['nullable', 'array'],
            'attachment_images.*' => ['image', 'max:10240'],
            'due_date' => ['nullable', 'date'],
            'total_score' => ['required', 'numeric', 'min:1'],
            'status' => ['required', 'string', 'max:255'],
        ]);

        $teacherAccess->authorizePair($request->user(), (int) $validated['school_class_id'], (int) $validated['subject_id']);

        $request->user()->assignments()->create([
            ...collect($validated)->except('attachment_images')->all(),
            'attachment_images' => $request->hasFile('attachment_images')
                ? $this->storeUploadedFiles($request->file('attachment_images'), 'teaching/assignment-images', Str::slug($validated['title']).'-assignment')
                : [],
        ]);

        return back()->with('status', 'Assignment created successfully.');
    }

    public function storeAssessment(Request $request, TeacherAccessService $teacherAccess): RedirectResponse
    {
        $validated = $request->validate([
            'term_id' => ['nullable', 'exists:terms,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:quiz,test,exam,project'],
            'total_score' => ['required', 'numeric', 'min:1'],
            'scheduled_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $teacherAccess->authorizePair($request->user(), (int) $validated['school_class_id'], (int) $validated['subject_id']);

        $request->user()->assessments()->create($validated);

        return back()->with('status', 'Assessment added successfully.');
    }

    public function storeResult(Request $request, TeacherAccessService $teacherAccess): RedirectResponse
    {
        $validated = $request->validate([
            'assessment_id' => ['required', 'exists:assessments,id'],
            'student_id' => ['required', 'exists:students,id'],
            'score' => ['required', 'numeric', 'min:0'],
            'grade' => ['nullable', 'string', 'max:10'],
            'remark' => ['nullable', 'string', 'max:255'],
        ]);

        $assessment = Assessment::query()
            ->with('schoolClass')
            ->findOrFail($validated['assessment_id']);
        $student = Student::query()->findOrFail($validated['student_id']);
        $teacherAccess->authorizePair($request->user(), (int) $assessment->school_class_id, (int) $assessment->subject_id);

        if ((float) $validated['score'] > (float) $assessment->total_score) {
            return back()->withErrors([
                'score' => 'The obtained score cannot be greater than the assessment total score.',
            ])->withInput();
        }

        if ((int) $student->school_class_id !== (int) $assessment->school_class_id) {
            return back()->withErrors([
                'student_id' => 'This student does not belong to the selected class assessment.',
            ])->withInput();
        }

        AssessmentResult::updateOrCreate(
            [
                'assessment_id' => $validated['assessment_id'],
                'student_id' => $validated['student_id'],
            ],
            $validated,
        );

        return back()->with('status', 'Result saved successfully.');
    }

    public function storeAttendance(Request $request, TeacherAccessService $teacherAccess): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'student_id' => ['required', 'exists:students,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', 'in:present,late,absent,excused'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $teacherAccess->authorizeClass($request->user(), (int) $validated['school_class_id']);
        $student = Student::query()->findOrFail($validated['student_id']);

        if ((int) $student->school_class_id !== (int) $validated['school_class_id']) {
            return back()->withErrors([
                'student_id' => 'This student does not belong to the selected class.',
            ])->withInput();
        }

        AttendanceRecord::updateOrCreate(
            [
                'school_class_id' => $validated['school_class_id'],
                'student_id' => $validated['student_id'],
                'attendance_date' => $validated['attendance_date'],
            ],
            [
                'taken_by' => $request->user()->id,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ],
        );

        return back()->with('status', 'Attendance updated.');
    }

    public function gradeSubmission(Request $request, AssignmentSubmission $submission, TeacherAccessService $teacherAccess): RedirectResponse
    {
        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->loadMissing('assignment.schoolClass', 'assignment.subject');
        $teacherAccess->authorizePair(
            $request->user(),
            (int) $submission->assignment->school_class_id,
            (int) $submission->assignment->subject_id,
        );

        if ((float) $validated['score'] > (float) $submission->assignment->total_score) {
            return back()->withErrors([
                'score' => 'The awarded score cannot be greater than the assignment total score.',
            ]);
        }

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'] ?? null,
            'graded_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Submission graded successfully.');
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
