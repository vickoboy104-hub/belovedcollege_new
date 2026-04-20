<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('temp_password_plaintext')->nullable()->after('password');
            $table->timestamp('temp_password_generated_at')->nullable()->after('temp_password_plaintext');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['temp_password_plaintext', 'temp_password_generated_at']);
        });
    }
};
