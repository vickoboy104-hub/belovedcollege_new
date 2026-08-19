<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingInputIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_store_rejects_unknown_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post(route('teacher.assignments.store'), [
            'status' => 'hidden-forged-state',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseCount('assignments', 0);
    }

    public function test_published_assignment_remains_valid(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $class = SchoolClass::create([
            'name' => 'JSS 1',
            'section' => 'A',
            'slug' => 'jss-1-a-teaching',
        ]);
        $subject = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MTH-TCH',
        ]);

        $response = $this->actingAs($admin)->post(route('teacher.assignments.store'), [
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Algebra practice',
            'instructions' => 'Complete all questions.',
            'total_score' => 20,
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Assignment::count());
        $this->assertDatabaseHas('assignments', [
            'title' => 'Algebra practice',
            'status' => 'published',
        ]);
    }

    public function test_attendance_cannot_be_recorded_for_future_date(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $class = SchoolClass::create([
            'name' => 'JSS 2',
            'section' => 'A',
            'slug' => 'jss-2-a-attendance',
        ]);
        $studentUser = User::factory()->create(['role' => UserRole::Student]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-ATT-001',
            'school_class_id' => $class->id,
        ]);

        $response = $this->actingAs($admin)->post(route('teacher.attendance.store'), [
            'school_class_id' => $class->id,
            'student_id' => $student->id,
            'attendance_date' => now()->addDay()->toDateString(),
            'status' => 'present',
        ]);

        $response->assertSessionHasErrors('attendance_date');
        $this->assertDatabaseCount('attendance_records', 0);
    }
}
