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
        if (! Schema::hasTable('notification_preferences')) {
            return;
        }

        Schema::table('notification_preferences', function (Blueprint $table) {
            if (! Schema::hasColumn('notification_preferences', 'pet_activity')) {
                $table->boolean('pet_activity')->default(false)->after('user_id');
            }

            if (! Schema::hasColumn('notification_preferences', 'appointment_created')) {
                $table->boolean('appointment_created')->default(false)->after('pet_activity');
            }

            if (! Schema::hasColumn('notification_preferences', 'caregiver_invitation')) {
                $table->boolean('caregiver_invitation')->default(false)->after('appointment_created');
            }

            if (! Schema::hasColumn('notification_preferences', 'gift_received')) {
                $table->boolean('gift_received')->default(false)->after('caregiver_invitation');
            }

            if (! Schema::hasColumn('notification_preferences', 'routine_reminder')) {
                $table->boolean('routine_reminder')->default(false)->after('gift_received');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('notification_preferences')) {
            return;
        }

        Schema::table('notification_preferences', function (Blueprint $table) {
            foreach (['routine_reminder', 'gift_received', 'caregiver_invitation', 'appointment_created', 'pet_activity'] as $column) {
                if (Schema::hasColumn('notification_preferences', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
