<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cabin_settings') && !Schema::hasColumn('cabin_settings', 'booking_shifts')) {
            Schema::table('cabin_settings', function (Blueprint $table) {
                $table->json('booking_shifts')->nullable()->after('clinic_close_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cabin_settings') && Schema::hasColumn('cabin_settings', 'booking_shifts')) {
            Schema::table('cabin_settings', function (Blueprint $table) {
                $table->dropColumn('booking_shifts');
            });
        }
    }
};
