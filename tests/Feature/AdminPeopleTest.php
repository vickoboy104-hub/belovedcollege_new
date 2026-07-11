<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\SchoolClass;
use App\Models\StaffProfile;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPeopleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_people_hub_and_open_dedicated_workspaces(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $class = SchoolClass::create(['name' => 'JSS 1', 'slug' => 'jss-1-general', 'section' => 'General']);
        $studentUser = User::factory()->create([
            'first_name' => 'Amina', 'last_name' => 'Yusuf', 'name' => 'Amina Yusuf',
            'email' => 'amina@example.test', 'role' => UserRole::Student,
        ]);
        Student::create([
            'user_id' => $studentUser->id, 'admission_no' => 'ADM-001',
            'student_id_no' => 'STD-001', 'school_class_id' => $class->id,
        ]);
        $staffUser = User::factory()->create([
            'first_name' => 'Daniel', 'last_name' => 'Adeyemi', 'name' => 'Daniel Adeyemi',
            'email' => 'daniel@example.test', 'role' => UserRole::Teacher,
        ]);
        StaffProfile::create([
            'user_id' => $staffUser->id, 'employee_no' => 'STF-001',
            'department' => 'Sciences', 'designation' => 'Physics Teacher',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.people'));

        $response->assertOk();
        $response->assertSee('People Hub');
        $response->assertSee('Dedicated management portals');
        $response->assertSee('Student Profiles');
        $response->assertSee('Guardians &amp; Sibling', false);
        $response->assertSee('Staff Directories');
        $response->assertSee('1 Records');
        $response->assertSee('1 Profiles');
    }

    public function test_admin_can_filter_people_by_class_and_department(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $jss1 = SchoolClass::create(['name' => 'JSS 1', 'slug' => 'jss-1-general', 'section' => 'General']);
        $jss2 = SchoolClass::create(['name' => 'JSS 2', 'slug' => 'jss-2-general', 'section' => 'General']);
        $studentOne = User::factory()->create([
            'first_name' => 'Amina', 'last_name' => 'Yusuf', 'name' => 'Amina Yusuf', 'role' => UserRole::Student,
        ]);
        $studentTwo = User::factory()->create([
            'first_name' => 'David', 'last_name' => 'Obi', 'name' => 'David Obi', 'role' => UserRole::Student,
        ]);
        Student::create(['user_id' => $studentOne->id, 'admission_no' => 'ADM-001', 'school_class_id' => $jss1->id]);
        Student::create(['user_id' => $studentTwo->id, 'admission_no' => 'ADM-002', 'school_class_id' => $jss2->id]);
        $teacher = User::factory()->create([
            'first_name' => 'Daniel', 'last_name' => 'Adeyemi', 'name' => 'Daniel Adeyemi', 'role' => UserRole::Teacher,
        ]);
        $accountant = User::factory()->create([
            'first_name' => 'Kemi', 'last_name' => 'Balogun', 'name' => 'Kemi Balogun', 'role' => UserRole::Accountant,
        ]);
        StaffProfile::create(['user_id' => $teacher->id, 'employee_no' => 'STF-001', 'department' => 'Sciences']);
        StaffProfile::create(['user_id' => $accountant->id, 'employee_no' => 'STF-002', 'department' => 'Accounts']);

        $response = $this->actingAs($admin)->get(route('admin.people', [
            'class_id' => $jss1->id,
            'department' => 'Sciences',
        ]));

        $response->assertOk();
        $response->assertSee('People Hub');
        $response->assertSee('2 Records');
        $response->assertSee('2 Profiles');
    }

    public function test_admin_can_open_full_student_profile_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $class = SchoolClass::create(['name' => 'JSS 1', 'slug' => 'jss-1-general', 'section' => 'General']);
        $studentUser = User::factory()->create([
            'first_name' => 'Amina', 'last_name' => 'Yusuf', 'name' => 'Amina Yusuf',
            'email' => 'amina@example.test', 'role' => UserRole::Student,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id, 'admission_no' => 'ADM-001',
            'student_id_no' => 'STD-001', 'school_class_id' => $class->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.students.show', $student));

        $response->assertOk();
        $response->assertSee('Edit full student profile');
        $response->assertSee('Amina Yusuf');
    }

    public function test_admin_can_update_student_details_from_people_directory(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $initialClass = SchoolClass::create(['name' => 'JSS 1', 'slug' => 'jss-1-general', 'section' => 'General']);
        $newClass = SchoolClass::create(['name' => 'JSS 2', 'slug' => 'jss-2-general', 'section' => 'General']);
        $studentUser = User::factory()->create([
            'first_name' => 'Amina', 'last_name' => 'Yusuf', 'name' => 'Amina Yusuf',
            'email' => 'amina@example.test', 'role' => UserRole::Student,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id, 'admission_no' => 'ADM-001', 'student_id_no' => 'STD-001',
            'school_class_id' => $initialClass->id, 'guardian_name' => 'Mrs Yusuf',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.students.update', $student), [
            'first_name' => 'Amina', 'middle_name' => 'Grace', 'last_name' => 'Bello',
            'email' => 'amina.bello@example.test', 'phone' => '08030000000',
            'admission_no' => 'ADM-002', 'student_id_no' => 'STD-002',
            'school_class_id' => $newClass->id, 'status' => 'active', 'gender' => 'Female',
            'guardian_name' => 'Mrs Bello', 'guardian_phone' => '08035555555',
            'redirect_search' => 'ADM-002',
        ]);

        $response->assertRedirect(route('admin.students.index', ['search' => 'ADM-002']));
        $this->assertDatabaseHas('users', [
            'id' => $studentUser->id, 'last_name' => 'Bello',
            'email' => 'amina.bello@example.test', 'phone' => '08030000000',
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $student->id, 'admission_no' => 'ADM-002', 'student_id_no' => 'STD-002',
            'school_class_id' => $newClass->id, 'guardian_name' => 'Mrs Bello',
            'guardian_phone' => '08035555555',
        ]);
    }

    public function test_admin_can_update_staff_details_from_people_directory(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $staffUser = User::factory()->create([
            'first_name' => 'Daniel', 'last_name' => 'Adeyemi', 'name' => 'Daniel Adeyemi',
            'email' => 'daniel@example.test', 'role' => UserRole::Teacher,
        ]);
        $profile = StaffProfile::create([
            'user_id' => $staffUser->id, 'employee_no' => 'STF-001',
            'department' => 'Sciences', 'designation' => 'Physics Teacher',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.staff.update', $profile), [
            'first_name' => 'Daniel', 'middle_name' => 'O.', 'last_name' => 'Adebayo',
            'email' => 'daniel.adebayo@example.test', 'phone' => '08031111111',
            'employee_no' => 'STF-002', 'role' => 'principal', 'department' => 'Leadership',
            'designation' => 'Vice Principal', 'qualification' => 'M.Ed', 'hire_date' => '2026-01-15',
            'status' => 'active', 'redirect_search' => 'Leadership',
        ]);

        $response->assertRedirect(route('admin.staff.index', [
            'search' => 'Leadership',
            'department' => 'Leadership',
        ]));
        $this->assertDatabaseHas('users', [
            'id' => $staffUser->id, 'last_name' => 'Adebayo',
            'email' => 'daniel.adebayo@example.test', 'phone' => '08031111111', 'role' => 'principal',
        ]);
        $this->assertDatabaseHas('staff_profiles', [
            'id' => $profile->id, 'employee_no' => 'STF-002', 'department' => 'Leadership',
            'designation' => 'Vice Principal', 'qualification' => 'M.Ed',
        ]);
    }

    public function test_admin_can_deactivate_student_and_delete_staff_records(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $studentUser = User::factory()->create(['role' => UserRole::Student]);
        $student = Student::create(['user_id' => $studentUser->id, 'admission_no' => 'ADM-100']);
        $staffUser = User::factory()->create(['role' => UserRole::Teacher]);
        $profile = StaffProfile::create([
            'user_id' => $staffUser->id, 'employee_no' => 'STF-100', 'department' => 'Sciences',
        ]);

        $deactivateResponse = $this->actingAs($admin)->patch(route('admin.students.deactivate', $student), [
            'redirect_search' => 'ADM-100',
        ]);

        $deactivateResponse->assertRedirect(route('admin.students.index', ['search' => 'ADM-100']));
        $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('users', ['id' => $studentUser->id, 'status' => 'inactive']);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.staff.destroy', $profile), [
            'redirect_department' => 'Sciences',
        ]);

        $deleteResponse->assertRedirect(route('admin.staff.index', ['department' => 'Sciences']));
        $this->assertDatabaseMissing('staff_profiles', ['id' => $profile->id]);
        $this->assertDatabaseMissing('users', ['id' => $staffUser->id]);
    }
}
