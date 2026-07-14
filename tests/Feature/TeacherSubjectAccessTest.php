<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherSubjectAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_grant_and_revoke_an_exact_teacher_class_subject_permission(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher, 'status' => 'active']);
        $schoolClass = SchoolClass::create(['name' => 'JSS 1', 'slug' => 'jss-1-general', 'section' => 'General']);
        $subject = Subject::create(['name' => 'Mathematics', 'code' => 'MTH']);

        $this->actingAs($admin)
            ->post(route('admin.teacher-access.store'), [
                'teacher_id' => $teacher->id,
                'school_class_id' => $schoolClass->id,
                'subject_id' => $subject->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $assignment = TeacherSubjectAssignment::query()->firstOrFail();
        $this->assertTrue($assignment->is_active);
        $this->assertSame($admin->id, $assignment->assigned_by);

        $this->actingAs($admin)
            ->patch(route('admin.teacher-access.revoke', $assignment))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertFalse($assignment->fresh()->is_active);
        $this->assertSame($admin->id, $assignment->fresh()->revoked_by);
    }

    public function test_teacher_only_sees_assigned_classes_and_subjects(): void
    {
        [$teacher, $assignedClass, $assignedSubject, $otherClass, $otherSubject] = $this->seedAccessScenario();

        $response = $this->actingAs($teacher)->get(route('teacher.learning'));

        $response->assertOk();
        $response->assertSee($assignedClass->display_name);
        $response->assertSee($assignedSubject->name);
        $response->assertDontSee($otherClass->display_name);
        $response->assertDontSee($otherSubject->name);

        $map = $this->actingAs($teacher)->getJson(route('teacher.access-map'));
        $map->assertOk()
            ->assertJsonPath('has_access', true)
            ->assertJsonPath('class_subject_map.'.$assignedClass->id.'.0', $assignedSubject->id);
    }

    public function test_teacher_can_create_content_only_for_an_assigned_pair(): void
    {
        [$teacher, $assignedClass, $assignedSubject, $otherClass, $otherSubject] = $this->seedAccessScenario();

        $this->actingAs($teacher)
            ->post(route('teacher.lessons.store'), [
                'school_class_id' => $assignedClass->id,
                'subject_id' => $assignedSubject->id,
                'title' => 'Assigned lesson',
                'body' => 'Lesson content',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('lessons', [
            'teacher_id' => $teacher->id,
            'school_class_id' => $assignedClass->id,
            'subject_id' => $assignedSubject->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.lessons.store'), [
                'school_class_id' => $assignedClass->id,
                'subject_id' => $otherSubject->id,
                'title' => 'Wrong subject',
                'body' => 'Must be rejected',
            ])
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post(route('teacher.lessons.store'), [
                'school_class_id' => $otherClass->id,
                'subject_id' => $assignedSubject->id,
                'title' => 'Wrong class',
                'body' => 'Must be rejected',
            ])
            ->assertForbidden();

        $this->assertSame(1, Lesson::query()->count());
    }

    public function test_revoking_permission_immediately_blocks_standard_and_cbt_creation(): void
    {
        [$teacher, $assignedClass, $assignedSubject] = $this->seedAccessScenario();
        $assignment = TeacherSubjectAssignment::query()->firstOrFail();
        $assignment->update(['is_active' => false, 'revoked_at' => now()]);

        $this->actingAs($teacher)
            ->post(route('teacher.assessments.store'), [
                'school_class_id' => $assignedClass->id,
                'subject_id' => $assignedSubject->id,
                'title' => 'Blocked test',
                'type' => 'test',
                'total_score' => 100,
            ])
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post(route('teacher.cbt.assessments.store'), [
                'school_class_id' => $assignedClass->id,
                'subject_id' => $assignedSubject->id,
                'title' => 'Blocked CBT',
                'type' => 'test',
                'cbt_duration_minutes' => 30,
            ])
            ->assertForbidden();
    }

    protected function seedAccessScenario(): array
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $assignedClass = SchoolClass::create(['name' => 'JSS 1', 'slug' => 'jss-1-general', 'section' => 'General']);
        $otherClass = SchoolClass::create(['name' => 'JSS 2', 'slug' => 'jss-2-general', 'section' => 'General']);
        $assignedSubject = Subject::create(['name' => 'Mathematics', 'code' => 'MTH']);
        $otherSubject = Subject::create(['name' => 'English Language', 'code' => 'ENG']);

        TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $assignedClass->id,
            'subject_id' => $assignedSubject->id,
            'assigned_by' => $admin->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        return [$teacher, $assignedClass, $assignedSubject, $otherClass, $otherSubject];
    }
}
