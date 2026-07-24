<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('is_after_slot')->default(false)->after('aptTime');
            $table->time('after_slot_start_time')->nullable()->after('is_after_slot');
            $table->index(['doctor_id', 'aptDate', 'is_after_slot'], 'payments_after_slot_tracking_idx');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('is_after_slot')->default(false)->after('time_slot');
            $table->time('after_slot_start_time')->nullable()->after('is_after_slot');
            $table->index(['doctor_id', 'date', 'is_after_slot'], 'appointments_after_slot_tracking_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_after_slot_tracking_idx');
            $table->dropColumn(['is_after_slot', 'after_slot_start_time']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_after_slot_tracking_idx');
            $table->dropColumn(['is_after_slot', 'after_slot_start_time']);
        });
    }
};
