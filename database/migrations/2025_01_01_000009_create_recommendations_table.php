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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insight_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('action_type'); // budget_adjustment, keyword_addition, ad_copy_change, etc.
            $table->string('estimated_impact')->nullable();
            $table->string('implementation_difficulty'); // easy, medium, hard
            $table->json('specific_actions')->nullable(); // 具体的な実施手順
            $table->string('status')->default('pending'); // pending, in_progress, implemented, dismissed
            $table->timestamp('implemented_at')->nullable();
            $table->timestamps();

            $table->index(['insight_id', 'status']);
            $table->index('action_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
