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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('student')->after('email');
            $table->string('phone')->nullable()->after('email_verified_at');
            $table->string('status')->default('active')->after('phone');
            $table->string('avatar_url')->nullable()->after('status');
            $table->timestamp('last_seen_at')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'phone', 'status', 'avatar_url', 'last_seen_at']);
        });
    }
};
