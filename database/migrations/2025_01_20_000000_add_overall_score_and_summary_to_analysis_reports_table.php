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
        Schema::table('analysis_reports', function (Blueprint $table) {
            $table->integer('overall_score')->nullable()->after('status');
            $table->text('summary')->nullable()->after('overall_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_reports', function (Blueprint $table) {
            $table->dropColumn(['overall_score', 'summary']);
        });
    }
};
