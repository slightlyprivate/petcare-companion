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
        Schema::table('gifts', function (Blueprint $table) {
            // Add gift_type_id as nullable foreign key to allow existing gifts without type
            $table->foreignUuid('gift_type_id')
                ->nullable()
                ->after('pet_id')
                ->constrained('gift_types')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gifts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gift_type_id');
        });
    }
};
