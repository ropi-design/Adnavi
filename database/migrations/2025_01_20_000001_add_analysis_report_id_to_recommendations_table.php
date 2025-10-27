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
        Schema::table('recommendations', function (Blueprint $table) {
            $table->foreignId('analysis_report_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['analysis_report_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropForeign(['analysis_report_id']);
            $table->dropColumn('analysis_report_id');
        });
    }
};
