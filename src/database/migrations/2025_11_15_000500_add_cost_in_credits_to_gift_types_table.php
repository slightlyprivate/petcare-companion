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
        Schema::table('gift_types', function (Blueprint $table) {
            if (! Schema::hasColumn('gift_types', 'cost_in_credits')) {
                $table->unsignedBigInteger('cost_in_credits')->default(100)->after('color_code');
                $table->index('cost_in_credits');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_types', function (Blueprint $table) {
            if (Schema::hasColumn('gift_types', 'cost_in_credits')) {
                $table->dropIndex(['cost_in_credits']);
                $table->dropColumn('cost_in_credits');
            }
        });
    }
};
