<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table): void {
            $table->string('video_path')->nullable()->after('video_url');
            $table->json('note_images')->nullable()->after('resource_link');
        });

        Schema::table('assignments', function (Blueprint $table): void {
            $table->json('attachment_images')->nullable()->after('instructions');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table): void {
            $table->dropColumn('attachment_images');
        });

        Schema::table('lessons', function (Blueprint $table): void {
            $table->dropColumn(['video_path', 'note_images']);
        });
    }
};
