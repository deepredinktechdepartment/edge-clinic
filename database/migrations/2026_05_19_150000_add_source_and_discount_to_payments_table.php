<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('source_id')->nullable()->after('doctor_id');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('registration_fee');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_percentage', 'source_id']);
        });
    }
};
