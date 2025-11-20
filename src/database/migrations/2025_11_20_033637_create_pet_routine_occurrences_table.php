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
        Schema::create('pet_routine_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_routine_id')->constrained('pet_routines')->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('completed_at')->nullable();
            $table->uuid('completed_by')->nullable();
            $table->foreign('completed_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['pet_routine_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_routine_occurrences');
    }
};
