<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_sessions', function (Blueprint $table): void {
            $table->decimal('promotion_pass_mark', 5, 2)->default(50)->after('end_date');
            $table->timestamp('closed_at')->nullable()->after('is_current');
            $table->foreignId('closed_by')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('student_promotions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('to_academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('from_school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('to_school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->string('promotion_status');
            $table->decimal('promotion_threshold', 5, 2)->default(50);
            $table->decimal('overall_percentage', 8, 2)->default(0);
            $table->decimal('subject_total_percentage', 8, 2)->default(0);
            $table->unsignedInteger('subject_count')->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'from_academic_session_id'], 'student_promotions_student_session_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotions');

        Schema::table('academic_sessions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('closed_by');
            $table->dropColumn(['promotion_pass_mark', 'closed_at']);
        });
    }
};
