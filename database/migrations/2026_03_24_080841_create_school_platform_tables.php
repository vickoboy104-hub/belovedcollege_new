<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group')->default('school');
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        Schema::create('academic_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        Schema::create('terms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        Schema::create('school_classes', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('section')->nullable();
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('room')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admission_no')->unique();
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('boarding_status')->nullable();
            $table->string('house')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('state_of_origin')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->text('address')->nullable();
            $table->text('medical_notes')->nullable();
            $table->date('enrolled_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('staff_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employee_no')->unique();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('qualification')->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('body');
            $table->string('video_url')->nullable();
            $table->string('resource_link')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('instructions');
            $table->timestamp('due_date')->nullable();
            $table->decimal('total_score', 8, 2)->default(100);
            $table->string('status')->default('published');
            $table->timestamps();
        });

        Schema::create('assignment_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->longText('content')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('assessments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('test');
            $table->decimal('total_score', 8, 2)->default(100);
            $table->timestamp('scheduled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('assessment_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 8, 2);
            $table->string('grade')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->unique(['assessment_id', 'student_id'], 'asmt_results_assmt_student_uniq');
        });

        Schema::create('attendance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('taken_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('attendance_date');
            $table->string('status')->default('present');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['school_class_id', 'student_id', 'attendance_date'], 'att_records_class_student_date_uniq');
        });

        Schema::create('fee_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('academic_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();
        });

        Schema::create('fee_invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_item_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2);
            $table->date('due_date')->nullable();
            $table->string('status')->default('unpaid');
            $table->timestamp('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('fee_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('reference')->unique();
            $table->string('gateway_reference')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('NGN');
            $table->string('status')->default('pending');
            $table->string('channel')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('category')->default('news');
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('new');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('fee_items');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('assessment_results');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('students');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('school_classes');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('academic_sessions');
        Schema::dropIfExists('settings');
    }
};
