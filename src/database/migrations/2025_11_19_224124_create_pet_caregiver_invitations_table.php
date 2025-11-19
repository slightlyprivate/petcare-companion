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
        Schema::create('pet_caregiver_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('pet_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('inviter_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('invitee_email');
            $table->foreignUuid('invitee_id')->nullable()->references('id')->on('users')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->enum('status', ['pending', 'accepted', 'revoked', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['token', 'status']);
            $table->index(['invitee_email', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_caregiver_invitations');
    }
};
