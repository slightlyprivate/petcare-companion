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
        Schema::table('credit_transactions', function (Blueprint $table) {
            // Add new columns for enhanced transaction tracking
            $table->integer('amount_credits')->nullable()->after('amount');
            $table->string('reason')->nullable()->after('type'); // e.g., 'gift_sent', 'purchase'
            $table->string('related_type')->nullable()->after('reason'); // e.g., 'gift', 'credit_purchase'
            $table->uuid('related_id')->nullable()->after('related_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->dropColumn(['amount_credits', 'reason', 'related_type', 'related_id']);
        });
    }
};
