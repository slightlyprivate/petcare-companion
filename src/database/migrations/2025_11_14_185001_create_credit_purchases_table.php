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
        Schema::create('credit_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('wallet_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('credit_bundle_id')->constrained()->onDelete('restrict');
            $table->unsignedInteger('credits');
            $table->unsignedInteger('amount_cents');
            $table->string('stripe_session_id')->nullable()->unique();
            $table->string('stripe_charge_id')->nullable()->unique();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_purchases');
    }
};
