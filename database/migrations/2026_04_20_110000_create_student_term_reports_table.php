<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_term_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->unsignedSmallInteger('days_school_open')->nullable();
            $table->unsignedSmallInteger('days_present')->nullable();
            $table->unsignedSmallInteger('days_absent')->nullable();
            $table->date('next_term_begins_on')->nullable();
            $table->json('character_traits')->nullable();
            $table->json('practical_skills')->nullable();
            $table->string('class_teacher_remark')->nullable();
            $table->string('guidance_remark')->nullable();
            $table->string('principal_remark')->nullable();
            $table->string('house_master_remark')->nullable();
            $table->string('overall_grade', 10)->nullable();
            $table->decimal('average_score', 6, 2)->nullable();
            $table->decimal('total_score', 8, 2)->nullable();
            $table->unsignedInteger('subject_count')->default(0);
            $table->unsignedInteger('class_position')->nullable();
            $table->boolean('portal_enabled')->default(false);
            $table->boolean('checker_enabled')->default(false);
            $table->string('checker_pin_hash')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'term_id'], 'student_term_report_student_term_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_term_reports');
    }
};
