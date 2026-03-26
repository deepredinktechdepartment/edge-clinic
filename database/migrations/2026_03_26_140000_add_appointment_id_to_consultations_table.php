<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->unsignedBigInteger('appointment_id')->nullable()->after('payment_id');
            $table->unique('appointment_id');
            $table->index(['appointment_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropIndex(['appointment_id', 'patient_id']);
            $table->dropUnique(['appointment_id']);
            $table->dropColumn('appointment_id');
        });
    }
};
