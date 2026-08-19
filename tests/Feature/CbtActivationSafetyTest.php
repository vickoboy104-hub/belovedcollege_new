<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\CbtQuestion;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtActivationSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_cbt_cannot_be_activated(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher]);
        $class = SchoolClass::create(['name' => 'SS 1', 'section' => 'A', 'slug' => 'ss-1-a-cbt']);
        $subject = Subject::create(['name' => 'Biology', 'code' => 'BIO-CBT']);
        $assessment = Assessment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Empty Biology CBT',
            'type' => 'test',
            'is_cbt' => true,
            'total_score' => 0,
            'cbt_duration_minutes' => 30,
            'cbt_is_active' => false,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.cbt.assessments.toggle', $assessment));

        $response->assertSessionHasErrors('assessment');
        $this->assertFalse($assessment->fresh()->cbt_is_active);
    }

    public function test_scored_cbt_can_be_activated_and_later_deactivated(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $teacher = User::factory()->create(['role' => UserRole::Teacher]);
        $class = SchoolClass::create(['name' => 'SS 2', 'section' => 'A', 'slug' => 'ss-2-a-cbt']);
        $subject = Subject::create(['name' => 'Physics', 'code' => 'PHY-CBT']);
        $assessment = Assessment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Physics CBT',
            'type' => 'test',
            'is_cbt' => true,
            'total_score' => 5,
            'cbt_duration_minutes' => 30,
            'cbt_is_active' => false,
        ]);
        CbtQuestion::create([
            'assessment_id' => $assessment->id,
            'question_type' => 'theory',
            'prompt' => 'State Newton’s first law.',
            'points' => 5,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.cbt.assessments.toggle', $assessment))
            ->assertSessionHas('status', 'CBT exam activated.');
        $this->assertTrue($assessment->fresh()->cbt_is_active);

        $this->actingAs($admin)
            ->patch(route('admin.cbt.assessments.toggle', $assessment->fresh()))
            ->assertSessionHas('status', 'CBT exam deactivated.');
        $this->assertFalse($assessment->fresh()->cbt_is_active);
    }
}
