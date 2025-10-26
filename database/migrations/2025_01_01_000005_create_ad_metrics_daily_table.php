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
        Schema::create('ad_metrics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->bigInteger('impressions')->default(0);
            $table->bigInteger('clicks')->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('conversions', 10, 2)->default(0);
            $table->decimal('conversion_value', 15, 2)->default(0);
            $table->decimal('ctr', 8, 4)->default(0);
            $table->decimal('cpc', 10, 2)->default(0);
            $table->decimal('cpa', 10, 2)->default(0);
            $table->decimal('roas', 10, 4)->default(0);
            $table->timestamps();

            $table->unique(['campaign_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_metrics_daily');
    }
};
