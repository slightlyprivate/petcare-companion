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
        Schema::create('pet_routines', function (Blueprint $table) {
            $table->id();
            $table->uuid('pet_id');
            $table->foreign('pet_id')->references('id')->on('pets')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->time('time_of_day');
            $table->json('days_of_week'); // e.g. [0,1,2,3,4,5,6] integers for Sun-Sat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_routines');
    }
};
