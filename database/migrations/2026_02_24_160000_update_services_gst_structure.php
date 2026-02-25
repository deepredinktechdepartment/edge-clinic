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
        Schema::table('services', function (Blueprint $table) {

            // Drop old columns
            $table->dropColumn([
                'billing_type',
                'gst_applicable',
                'gst_percentage'
            ]);

            // Add new GST columns
            $table->decimal('cgst', 5, 2)->default(0)->after('amount');
            $table->decimal('sgst', 5, 2)->default(0)->after('cgst');
            $table->decimal('igst', 5, 2)->default(0)->after('sgst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
