<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\FeeInvoice;
use App\Models\FeeItem;
use App\Models\SchoolClass;
use App\Models\Student;
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
