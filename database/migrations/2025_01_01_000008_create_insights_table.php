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
        Schema::create('insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_report_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // performance, budget, targeting, creative, conversion
            $table->string('priority'); // high, medium, low
            $table->string('title');
            $table->text('description');
            $table->integer('impact_score')->default(5); // 1-10
            $table->decimal('confidence_score', 3, 2)->default(0.5); // 0-1
            $table->timestamps();

            $table->index(['analysis_report_id', 'priority']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};
