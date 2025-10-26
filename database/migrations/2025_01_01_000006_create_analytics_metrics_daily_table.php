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
        Schema::create('analytics_metrics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analytics_property_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->bigInteger('sessions')->default(0);
            $table->bigInteger('users')->default(0);
            $table->bigInteger('new_users')->default(0);
            $table->bigInteger('pageviews')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->decimal('avg_session_duration', 10, 2)->default(0);
            $table->decimal('conversions', 10, 2)->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['analytics_property_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_metrics_daily');
    }
};
