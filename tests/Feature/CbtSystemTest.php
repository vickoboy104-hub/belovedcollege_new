<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\Assessment;
use App\Models\CbtAnswer;
use App\Models\CbtAttempt;
use App\Models\CbtQuestion;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CbtSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('uploads/cbt'));

        parent::tearDown();
    }

    public function test_teacher_can_create_cbt_assessment_and_add_questions_with_media(): void
    {
        [$teacher, $class, $subject, $term] = $this->teacherContext();

        $this->actingAs($teacher)->post(route('teacher.cbt.assessments.store'), [
            'term_id' => $term->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'SS1 Physics CBT',
            'type' => 'test',
            'cbt_duration_minutes' => 45,
            'cbt_instructions' => 'Read all questions carefully.',
            'cbt_show_results' => 1,
        ])->assertRedirect(route('teacher.learning'));

        $assessment = Assessment::query()->where('title', 'SS1 Physics CBT')->firstOrFail();

        $response = $this->actingAs($teacher)->post(route('teacher.cbt.questions.store', $assessment), [
            'question_type' => 'objective',
            'prompt' => 'Identify the correct law of motion.',
            'points' => 2,
            'image_paths' => [
                UploadedFile::fake()->image('law.png'),
            ],
            'video_url' => 'https://example.com/cbt-law-video',
            'resource_link' => 'https://example.com/cbt-law-note',
            'options' => ['First law', 'Second law', 'Third law', 'Hooke law'],
            'correct_option' => 1,
        ]);

        $response->assertSessionHas('status', 'CBT question added successfully.');

        $assessment->refresh();
        $question = $assessment->cbtQuestions()->with('options')->first();

        $this->assertTrue($assessment->is_cbt);
        $this->assertSame('objective', $question->question_type);
        $this->assertCount(4, $question->options);
        $this->assertSame('Second law', $question->options->firstWhere('is_correct', true)->option_text);
        $this->assertEquals(2.0, (float) $assessment->total_score);
        $this->assertFileExists(public_path($question->image_paths[0]));
    }

    public function test_admin_can_toggle_cbt_visibility_for_students(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        [$teacher, $class, $subject, $term, $student] = $this->teacherContext(withStudent: true);

        $assessment = Assessment::create([
            'teacher_id' => $teacher->id,
            'term_id' => $term->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Biology CBT Visibility Test',
            'type' => 'test',
            'is_cbt' => true,
            'total_score' => 5,
            'cbt_duration_minutes' => 30,
            'cbt_is_active' => true,
            'cbt_show_results' => true,
        ]);

        CbtQuestion::create([
            'assessment_id' => $assessment->id,
            'question_type' => 'theory',
            'prompt' => 'Explain photosynthesis.',
            'points' => 5,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)->post(route('admin.cbt.toggle'), [
            'enabled' => 0,
        ])->assertSessionHas('status', 'School CBT is now disabled.');

        $offResponse = $this->actingAs($student->user)->get(route('portal.index'));
        $offResponse->assertDontSee('Biology CBT Visibility Test');

        $this->actingAs($admin)->post(route('admin.cbt.toggle'), [
            'enabled' => 1,
        ])->assertSessionHas('status', 'School CBT is now enabled.');

        $onResponse = $this->actingAs($student->user)->get(route('portal.index'));
        $onResponse->assertSee('Biology CBT Visibility Test');
    }

    public function test_student_can_submit_cbt_and_teacher_can_grade_theory_answers(): void
    {
        [$teacher, $class, $subject, $term, $student] = $this->teacherContext(withStudent: true);

        Setting::setMany([
            'cbt_enabled' => '1',
        ]);

        $assessment = Assessment::create([
            'teacher_id' => $teacher->id,
            'term_id' => $term->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Mixed CBT Test',
            'type' => 'test',
            'is_cbt' => true,
            'total_score' => 7,
            'cbt_duration_minutes' => 30,
            'cbt_is_active' => true,
            'cbt_show_results' => true,
        ]);

        $objectiveQuestion = CbtQuestion::create([
            'assessment_id' => $assessment->id,
            'question_type' => 'objective',
            'prompt' => '2 + 2 = ?',
            'points' => 2,
            'sort_order' => 1,
        ]);

        $correctOption = $objectiveQuestion->options()->create([
            'option_text' => '4',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        $objectiveQuestion->options()->createMany([
            ['option_text' => '3', 'is_correct' => false, 'sort_order' => 2],
            ['option_text' => '5', 'is_correct' => false, 'sort_order' => 3],
            ['option_text' => '6', 'is_correct' => false, 'sort_order' => 4],
        ]);

        $theoryQuestion = CbtQuestion::create([
            'assessment_id' => $assessment->id,
            'question_type' => 'theory',
            'prompt' => 'Explain Newton’s first law.',
            'points' => 5,
            'sort_order' => 2,
        ]);

        $studentUser = $student->user;

        $this->actingAs($studentUser)->get(route('portal.cbt.show', $assessment))
            ->assertOk()
            ->assertSee('Mixed CBT Test');

        $this->actingAs($studentUser)->post(route('portal.cbt.submit', $assessment), [
            'answers' => [
                $objectiveQuestion->id => ['option' => $correctOption->id],
                $theoryQuestion->id => ['text' => 'It says an object remains in its state unless acted on by an external force.'],
            ],
        ])->assertRedirect(route('portal.index'));

        $attempt = CbtAttempt::query()->where('assessment_id', $assessment->id)->where('student_id', $student->id)->firstOrFail();

        $this->assertSame('submitted', $attempt->status);
        $this->assertEquals(2.0, (float) $attempt->objective_score);

        $theoryAnswer = CbtAnswer::query()
            ->where('cbt_attempt_id', $attempt->id)
            ->where('cbt_question_id', $theoryQuestion->id)
            ->firstOrFail();

        $this->actingAs($teacher)->post(route('teacher.cbt.answers.grade', $theoryAnswer), [
            'awarded_score' => 4,
            'feedback' => 'Good explanation.',
        ])->assertSessionHas('status', 'Theory answer graded successfully.');

        $attempt->refresh();

        $this->assertSame('graded', $attempt->status);
        $this->assertEquals(6.0, (float) $attempt->total_score);
        $this->assertDatabaseHas('assessment_results', [
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'score' => 6,
        ]);
    }

    protected function teacherContext(bool $withStudent = false): array
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'name' => 'Daniel Adeyemi',
        ]);

        $class = SchoolClass::create([
            'name' => 'SS 1',
            'slug' => 'ss-1-general',
            'section' => 'General',
            'class_teacher_id' => $teacher->id,
        ]);

        $subject = Subject::create([
            'name' => 'Physics',
            'code' => 'PHY101',
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'assigned_by' => $admin->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'slug' => 'first-term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-18',
            'is_current' => true,
        ]);

        if (! $withStudent) {
            return [$teacher, $class, $subject, $term];
        }

        $studentUser = User::factory()->create([
            'role' => UserRole::Student,
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'name' => 'Amina Yusuf',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-1001',
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
        ]);

        $student->setRelation('user', $studentUser);

        return [$teacher, $class, $subject, $term, $student];
    }
}
