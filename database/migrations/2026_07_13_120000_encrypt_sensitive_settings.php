<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            Setting::encryptExistingSensitiveValues();
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            Setting::decryptExistingSensitiveValues();
        }
    }
};
