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
        if (!Schema::hasColumn('insights', 'data_points')) {
            $afterColumn = Schema::hasColumn('insights', 'status') ? 'status' : 'confidence_score';

            Schema::table('insights', function (Blueprint $table) use ($afterColumn) {
                $table->longText('data_points')->nullable()->after($afterColumn);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insights', function (Blueprint $table) {
            $table->dropColumn('data_points');
        });
    }
};
