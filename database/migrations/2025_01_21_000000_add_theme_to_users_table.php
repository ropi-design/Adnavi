<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'theme')) {
            if (Schema::hasColumn('users', 'email_verified_at')) {
                DB::statement("ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(255) DEFAULT 'dark' AFTER `email_verified_at`");
            } else {
                DB::statement("ALTER TABLE `users` ADD COLUMN `theme` VARCHAR(255) DEFAULT 'dark' AFTER `email`");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'theme')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('theme');
            });
        }
    }
};
