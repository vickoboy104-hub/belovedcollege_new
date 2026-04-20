<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table): void {
            $table->boolean('is_cbt')->default(false)->after('type');
            $table->unsignedInteger('cbt_duration_minutes')->nullable()->after('total_score');
            $table->timestamp('cbt_starts_at')->nullable()->after('scheduled_at');
            $table->timestamp('cbt_ends_at')->nullable()->after('cbt_starts_at');
            $table->longText('cbt_instructions')->nullable()->after('notes');
            $table->boolean('cbt_is_active')->default(false)->after('cbt_instructions');
            $table->boolean('cbt_show_results')->default(false)->after('cbt_is_active');
        });

        Schema::create('cbt_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->string('question_type')->default('objective');
            $table->longText('prompt');
            $table->decimal('points', 8, 2)->default(1);
            $table->json('image_paths')->nullable();
            $table->string('video_path')->nullable();
            $table->string('video_url')->nullable();
            $table->string('resource_link')->nullable();
            $table->longText('theory_sample_answer')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('cbt_question_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cbt_question_id')->constrained()->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('cbt_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('objective_score', 8, 2)->default(0);
            $table->decimal('theory_score', 8, 2)->default(0);
            $table->decimal('total_score', 8, 2)->default(0);
            $table->timestamps();
            $table->unique(['assessment_id', 'student_id'], 'cbt_attempts_assessment_student_unique');
        });

        Schema::create('cbt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cbt_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cbt_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('selected_option_id')->nullable()->constrained('cbt_question_options')->nullOnDelete();
            $table->longText('answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('awarded_score', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
            $table->unique(['cbt_attempt_id', 'cbt_question_id'], 'cbt_answers_attempt_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_answers');
        Schema::dropIfExists('cbt_attempts');
        Schema::dropIfExists('cbt_question_options');
        Schema::dropIfExists('cbt_questions');

        Schema::table('assessments', function (Blueprint $table): void {
            $table->dropColumn([
                'is_cbt',
                'cbt_duration_minutes',
                'cbt_starts_at',
                'cbt_ends_at',
                'cbt_instructions',
                'cbt_is_active',
                'cbt_show_results',
            ]);
        });
    }
};
