<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->string('case_sheet_front_path')->nullable()->after('gcs');
            $table->string('case_sheet_back_path')->nullable()->after('case_sheet_front_path');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'case_sheet_front_path',
                'case_sheet_back_path',
            ]);
        });
    }
};
