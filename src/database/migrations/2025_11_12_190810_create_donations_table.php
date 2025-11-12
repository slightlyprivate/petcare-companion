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
        Schema::create('donations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('amount_cents');

            $stripeSessionId = $table->string('stripe_session_id')->nullable();
            // Apply utf8_bin collation only for MySQL
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $stripeSessionId->collation('utf8_bin');
            }

            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['pet_id', 'status']);
            $table->index('stripe_session_id');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
