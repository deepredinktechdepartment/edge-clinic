<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->date('follow_up_date')->nullable()->after('appointment_status');
            $table->index('follow_up_date');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->date('follow_up_date')->nullable()->after('appointment_status');
            $table->index('follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['follow_up_date']);
            $table->dropColumn('follow_up_date');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['follow_up_date']);
            $table->dropColumn('follow_up_date');
        });
    }
};
