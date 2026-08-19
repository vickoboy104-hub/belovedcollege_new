<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicSetupIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_term_name_in_same_session_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $session = $this->createSession();
        Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'slug' => 'first-term-existing',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-15',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.terms.store'), [
            'academic_session_id' => $session->id,
            'name' => 'first term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-15',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('terms', 1);
    }

    public function test_term_dates_must_fit_inside_academic_session(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $session = $this->createSession();

        $tooEarly = $this->actingAs($admin)->post(route('admin.terms.store'), [
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-08-31',
            'end_date' => '2026-12-15',
        ]);
        $tooEarly->assertSessionHasErrors('start_date');

        $tooLate = $this->actingAs($admin)->post(route('admin.terms.store'), [
            'academic_session_id' => $session->id,
            'name' => 'Third Term',
            'start_date' => '2027-04-20',
            'end_date' => '2027-08-01',
        ]);
        $tooLate->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('terms', 0);
    }

    public function test_current_term_must_belong_to_current_session(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $session = $this->createSession(isCurrent: false);

        $response = $this->actingAs($admin)->post(route('admin.terms.store'), [
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-15',
            'is_current' => true,
        ]);

        $response->assertSessionHasErrors('academic_session_id');
        $this->assertDatabaseCount('terms', 0);
    }

    public function test_closed_session_rejects_new_terms(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $session = $this->createSession(isCurrent: false);
        $session->update(['closed_at' => now(), 'closed_by' => $admin->id]);

        $response = $this->actingAs($admin)->post(route('admin.terms.store'), [
            'academic_session_id' => $session->id,
            'name' => 'Late Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-15',
        ]);

        $response->assertSessionHasErrors('academic_session_id');
        $this->assertDatabaseCount('terms', 0);
    }

    public function test_valid_current_term_can_be_created_in_current_session(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $session = $this->createSession();

        $response = $this->actingAs($admin)->post(route('admin.terms.store'), [
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-15',
            'is_current' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('terms', [
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'is_current' => 1,
        ]);
    }

    public function test_duplicate_class_and_section_combination_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        SchoolClass::create([
            'name' => 'JSS 1',
            'section' => 'A',
            'slug' => 'jss-1-a-existing',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.classes.store'), [
            'name' => 'jss 1',
            'section' => 'a',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('school_classes', 1);
    }

    public function test_same_class_name_can_be_used_for_a_different_section(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        SchoolClass::create([
            'name' => 'JSS 1',
            'section' => 'A',
            'slug' => 'jss-1-a-existing',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.classes.store'), [
            'name' => 'JSS 1',
            'section' => 'B',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('school_classes', 2);
    }

    public function test_inactive_staff_cannot_be_assigned_as_class_teacher(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.classes.store'), [
            'name' => 'JSS 2',
            'section' => 'A',
            'class_teacher_id' => $teacher->id,
        ]);

        $response->assertSessionHasErrors('class_teacher_id');
        $this->assertDatabaseCount('school_classes', 0);
    }

    public function test_duplicate_subject_name_is_rejected_case_insensitively(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Subject::create(['name' => 'Mathematics', 'code' => 'MTH']);

        $response = $this->actingAs($admin)->post(route('admin.subjects.store'), [
            'name' => 'mathematics',
            'code' => 'MATH',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('subjects', 1);
    }

    protected function createSession(bool $isCurrent = true): AcademicSession
    {
        return AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => $isCurrent,
        ]);
    }
}
