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
        Schema::create('analysis_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ad_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('analytics_property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('report_type'); // daily, weekly, monthly, custom
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->json('raw_data')->nullable(); // 分析に使用した生データ
            $table->json('analysis_result')->nullable(); // Geminiの分析結果
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_reports');
    }
};
