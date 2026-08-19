<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentDirectoryStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_directory_renders_the_actual_parent_account_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $parent = User::factory()->create([
            'name' => 'Mrs Yusuf',
            'email' => 'parent@example.test',
            'role' => UserRole::Parent,
            'status' => 'inactive',
        ]);
        $studentUser = User::factory()->create([
            'name' => 'Amina Yusuf',
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'role' => UserRole::Student,
        ]);
        Student::create([
            'user_id' => $studentUser->id,
            'parent_user_id' => $parent->id,
            'admission_no' => 'ADM-200',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.parents.index'));

        $response->assertOk();
        $response->assertSee('Inactive Parent - 1 child', false);
        $response->assertSee('Inactive');
    }
}
