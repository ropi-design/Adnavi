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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_account_id')->constrained()->cascadeOnDelete();
            $table->string('campaign_id')->unique(); // Google Campaign ID
            $table->string('campaign_name');
            $table->string('campaign_type')->nullable();
            $table->string('status')->default('active');
            $table->decimal('budget_amount', 15, 2)->nullable();
            $table->string('budget_type')->nullable();
            $table->timestamps();

            $table->index(['ad_account_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
