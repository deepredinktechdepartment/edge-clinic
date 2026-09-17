<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('doctors', 'registration_number')) {
            return;
        }

        Schema::table('doctors', function (Blueprint $table) {
            $table->string('registration_number', 100)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('doctors', 'registration_number')) {
            return;
        }

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('registration_number');
        });
    }
};
