<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('password');
            }

            if (! Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('avatar_url');
            }
        });

        if (Schema::hasColumn('users', 'temp_password_plaintext')) {
            DB::table('users')
                ->whereNotNull('temp_password_plaintext')
                ->update([
                    'temp_password_plaintext' => null,
                    'must_change_password' => true,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $columns = [];

            if (Schema::hasColumn('users', 'must_change_password')) {
                $columns[] = 'must_change_password';
            }

            if (Schema::hasColumn('users', 'avatar_path')) {
                $columns[] = 'avatar_path';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
