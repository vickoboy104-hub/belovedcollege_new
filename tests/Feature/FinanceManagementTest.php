<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\FeeItem;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_duplicate_fee_item_for_same_scope(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $class = SchoolClass::create([
            'name' => 'JSS 1',
            'slug' => 'jss-1-general',
            'section' => 'General',
        ]);

        FeeItem::create([
            'name' => 'Tuition Fee',
            'school_class_id' => $class->id,
            'amount' => 15000,
            'is_mandatory' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.fee-items.store'), [
            'name' => 'Tuition Fee',
            'school_class_id' => $class->id,
            'amount' => 15000,
            'is_mandatory' => 1,
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertSame(1, FeeItem::count());
    }

    public function test_fee_item_term_automatically_uses_its_academic_session(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'slug' => 'first-term-2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-15',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.fee-items.store'), [
            'name' => 'First Term Tuition',
            'term_id' => $term->id,
            'amount' => 25000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_items', [
            'name' => 'First Term Tuition',
            'term_id' => $term->id,
            'academic_session_id' => $session->id,
        ]);
    }

    public function test_fee_item_rejects_term_from_different_session(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $sessionOne = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
        ]);
        $sessionTwo = AcademicSession::create([
            'name' => '2027/2028',
            'start_date' => '2027-09-01',
            'end_date' => '2028-07-31',
        ]);
        $term = Term::create([
            'academic_session_id' => $sessionOne->id,
            'name' => 'First Term',
            'slug' => 'first-term-2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-15',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.fee-items.store'), [
            'name' => 'Mismatched Tuition',
            'academic_session_id' => $sessionTwo->id,
            'term_id' => $term->id,
            'amount' => 25000,
        ]);

        $response->assertSessionHasErrors('term_id');
        $this->assertDatabaseMissing('fee_items', ['name' => 'Mismatched Tuition']);
    }

    public function test_admin_invoice_generation_skips_duplicate_fee_invoice(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $class = SchoolClass::create([
            'name' => 'JSS 2',
            'slug' => 'jss-2-general',
            'section' => 'General',
        ]);

        $studentUser = User::factory()->create([
            'role' => UserRole::Student,
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'name' => 'Amina Yusuf',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-001',
            'school_class_id' => $class->id,
        ]);

        $feeItem = FeeItem::create([
            'name' => 'Exam Fee',
            'school_class_id' => $class->id,
            'amount' => 5000,
            'is_mandatory' => true,
        ]);

        FeeInvoice::create([
            'invoice_no' => 'INV-TEST-001',
            'student_id' => $student->id,
            'fee_item_id' => $feeItem->id,
            'amount_due' => 5000,
            'amount_paid' => 0,
            'balance' => 5000,
            'status' => 'unpaid',
            'issued_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.invoices.store'), [
            'fee_item_id' => $feeItem->id,
            'student_id' => $student->id,
        ]);

        $response->assertSessionHas('status', 'No new invoices were created because matching fee invoices already exist for the selected student(s).');
        $this->assertSame(1, FeeInvoice::count());
    }

    public function test_invoice_rejects_student_and_class_scope_at_same_time(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $class = SchoolClass::create(['name' => 'JSS 1', 'slug' => 'jss-1-a', 'section' => 'A']);
        $studentUser = User::factory()->create(['role' => UserRole::Student]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-010',
            'school_class_id' => $class->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.invoices.store'), [
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'amount_due' => 5000,
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('fee_invoices', 0);
    }

    public function test_direct_invoice_requires_positive_amount(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $studentUser = User::factory()->create(['role' => UserRole::Student]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-011',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.invoices.store'), [
            'student_id' => $student->id,
        ]);

        $response->assertSessionHasErrors('amount_due');
        $this->assertDatabaseCount('fee_invoices', 0);
    }

    public function test_class_scoped_fee_item_cannot_be_billed_to_student_in_another_class(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $jssOne = SchoolClass::create(['name' => 'JSS 1', 'slug' => 'jss-1-general', 'section' => 'General']);
        $jssTwo = SchoolClass::create(['name' => 'JSS 2', 'slug' => 'jss-2-general', 'section' => 'General']);
        $studentUser = User::factory()->create(['role' => UserRole::Student]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-012',
            'school_class_id' => $jssTwo->id,
        ]);
        $feeItem = FeeItem::create([
            'name' => 'JSS 1 Tuition',
            'school_class_id' => $jssOne->id,
            'amount' => 30000,
            'is_mandatory' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.invoices.store'), [
            'fee_item_id' => $feeItem->id,
            'student_id' => $student->id,
        ]);

        $response->assertSessionHasErrors('fee_item_id');
        $this->assertDatabaseCount('fee_invoices', 0);
    }

    public function test_admin_can_delete_fee_item(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $feeItem = FeeItem::create([
            'name' => 'PTA Levy',
            'amount' => 2500,
            'is_mandatory' => false,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.fee-items.destroy', $feeItem));

        $response->assertSessionHas('status', 'PTA Levy deleted successfully.');
        $this->assertDatabaseMissing('fee_items', [
            'id' => $feeItem->id,
        ]);
    }
}
