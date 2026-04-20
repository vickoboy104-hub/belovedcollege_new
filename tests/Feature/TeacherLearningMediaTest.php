<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TeacherLearningMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('uploads/teaching'));

        parent::tearDown();
    }

    public function test_teacher_can_publish_lesson_with_uploaded_video_and_note_images(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
        ]);

        $class = SchoolClass::create([
            'name' => 'SS 1',
            'slug' => 'ss-1-general',
            'section' => 'General',
        ]);

        $subject = Subject::create([
            'name' => 'Biology',
            'code' => 'BIO101',
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.lessons.store'), [
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Cell Structure',
            'summary' => 'Quick introduction to the cell.',
            'body' => 'Lesson note body.',
            'video_file' => UploadedFile::fake()->create('cell-structure.mp4', 2048, 'video/mp4'),
            'note_images' => [
                UploadedFile::fake()->image('cell-diagram.png'),
            ],
        ]);

        $response->assertSessionHas('status', 'Lesson published successfully.');

        $lesson = Lesson::first();

        $this->assertNotNull($lesson);
        $this->assertNotNull($lesson->video_path);
        $this->assertCount(1, $lesson->note_images ?? []);
        $this->assertFileExists(public_path($lesson->video_path));
        $this->assertFileExists(public_path($lesson->note_images[0]));
    }

    public function test_teacher_can_create_assignment_with_uploaded_images(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
        ]);

        $class = SchoolClass::create([
            'name' => 'SS 2',
            'slug' => 'ss-2-general',
            'section' => 'General',
        ]);

        $subject = Subject::create([
            'name' => 'Physics',
            'code' => 'PHY101',
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.assignments.store'), [
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Draw an electric circuit',
            'instructions' => 'Study the diagram and redraw it.',
            'total_score' => 20,
            'status' => 'published',
            'attachment_images' => [
                UploadedFile::fake()->image('circuit.png'),
            ],
        ]);

        $response->assertSessionHas('status', 'Assignment created successfully.');

        $assignment = Assignment::first();

        $this->assertNotNull($assignment);
        $this->assertCount(1, $assignment->attachment_images ?? []);
        $this->assertFileExists(public_path($assignment->attachment_images[0]));
    }
}
