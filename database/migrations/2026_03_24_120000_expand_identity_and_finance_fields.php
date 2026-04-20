<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('first_name')->nullable()->after('id');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->string('student_id_no')->nullable()->after('admission_no');
            $table->string('place_of_birth')->nullable()->after('date_of_birth');
            $table->string('nationality')->nullable()->after('place_of_birth');
            $table->string('lga')->nullable()->after('nationality');
            $table->string('religion')->nullable()->after('state_of_origin');
            $table->string('parents_occupation')->nullable()->after('religion');
            $table->string('office_residence_phone')->nullable()->after('guardian_phone');
            $table->string('previous_school')->nullable()->after('address');
            $table->string('previous_class')->nullable()->after('previous_school');
            $table->text('physical_notes')->nullable()->after('medical_notes');
            $table->string('doctor_name')->nullable()->after('physical_notes');
            $table->string('doctor_address')->nullable()->after('doctor_name');
            $table->string('doctor_phone')->nullable()->after('doctor_address');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->string('receipt_no')->nullable()->after('reference');
            $table->foreignId('recorded_by')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();
            $table->text('note')->nullable()->after('recorded_by');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('recorded_by');
            $table->dropColumn(['receipt_no', 'note']);
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->dropColumn([
                'student_id_no',
                'place_of_birth',
                'nationality',
                'lga',
                'religion',
                'parents_occupation',
                'office_residence_phone',
                'previous_school',
                'previous_class',
                'physical_notes',
                'doctor_name',
                'doctor_address',
                'doctor_phone',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });
    }
};
