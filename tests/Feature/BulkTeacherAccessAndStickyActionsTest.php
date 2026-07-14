<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkTeacherAccessAndStickyActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_grant_multiple_subjects_to_all_active_teachers(): void
    {
        $admin = $this->admin();
        $teachers = User::factory()->count(3)->create([
            'role' => UserRole::Teacher,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $class = SchoolClass::create([
            'name' => 'JSS 1',
            'slug' => 'jss-1-general',
            'section' => 'General',
        ]);
        $subjects = collect([
            Subject::create(['name' => 'Mathematics', 'code' => 'MTH101']),
            Subject::create(['name' => 'English Language', 'code' => 'ENG101']),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.teacher-access.store'), [
                'all_teachers' => 1,
                'school_class_ids' => [$class->id],
                'subject_ids' => $subjects->pluck('id')->all(),
            ])
            ->assertRedirect();

        $this->assertSame(6, TeacherSubjectAssignment::query()->where('is_active', true)->count());

        foreach ($teachers as $teacher) {
            foreach ($subjects as $subject) {
                $this->assertDatabaseHas('teacher_subject_assignments', [
                    'teacher_id' => $teacher->id,
                    'school_class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'is_active' => true,
                ]);
            }
        }
    }

    public function test_admin_can_grant_multiple_classes_and_subjects_to_selected_teachers(): void
    {
        $admin = $this->admin();
        $teachers = User::factory()->count(2)->create([
            'role' => UserRole::Teacher,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $classes = collect([
            SchoolClass::create(['name' => 'JSS 1', 'slug' => 'jss-1-general', 'section' => 'General']),
            SchoolClass::create(['name' => 'JSS 2', 'slug' => 'jss-2-general', 'section' => 'General']),
        ]);
        $subjects = collect([
            Subject::create(['name' => 'Basic Science', 'code' => 'BSC101']),
            Subject::create(['name' => 'Social Studies', 'code' => 'SOS101']),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.teacher-access.store'), [
                'teacher_ids' => $teachers->pluck('id')->all(),
                'school_class_ids' => $classes->pluck('id')->all(),
                'subject_ids' => $subjects->pluck('id')->all(),
            ])
            ->assertRedirect();

        $this->assertSame(8, TeacherSubjectAssignment::query()->where('is_active', true)->count());
    }

    public function test_admin_can_revoke_and_restore_selected_permissions_together(): void
    {
        $admin = $this->admin();
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $class = SchoolClass::create([
            'name' => 'SS 1',
            'slug' => 'ss-1-general',
            'section' => 'General',
        ]);
        $subjects = collect([
            Subject::create(['name' => 'Physics', 'code' => 'PHY101']),
            Subject::create(['name' => 'Chemistry', 'code' => 'CHE101']),
        ]);
        $assignments = $subjects->map(fn (Subject $subject) => TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'assigned_by' => $admin->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]));

        $this->actingAs($admin)
            ->patch(route('admin.teacher-access.bulk'), [
                'assignment_ids' => $assignments->pluck('id')->all(),
                'action' => 'revoke',
            ])
            ->assertRedirect();

        $this->assertSame(0, TeacherSubjectAssignment::query()->where('is_active', true)->count());

        $this->actingAs($admin)
            ->patch(route('admin.teacher-access.bulk'), [
                'assignment_ids' => $assignments->pluck('id')->all(),
                'action' => 'restore',
            ])
            ->assertRedirect();

        $this->assertSame(2, TeacherSubjectAssignment::query()->where('is_active', true)->count());
    }

    public function test_admin_pages_load_bulk_controls_and_navigation_shortcuts(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.teacher-access.index'))
            ->assertOk()
            ->assertSee('Select every active teacher')
            ->assertSee('Grant Selected Access')
            ->assertSee('Remove Selected')
            ->assertSee('admin-navigation-shortcuts.js', false)
            ->assertSee(route('admin.payment-gateways.index'), false);
    }

    public function test_shared_table_component_marks_real_action_columns_as_sticky(): void
    {
        $component = file_get_contents(resource_path('views/components/data-table.blade.php'));
        $stylesheet = file_get_contents(public_path('table-usability.css'));
        $reportStylesheet = file_get_contents(public_path('report-search-controls.css'));

        $this->assertStringContainsString('has-sticky-actions', $component);
        $this->assertStringContainsString('tbody td:last-child:not([colspan])', $stylesheet);
        $this->assertStringContainsString('height: 2.75rem !important', $reportStylesheet);
    }

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
