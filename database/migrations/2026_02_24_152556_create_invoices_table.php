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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_number')->unique();

            $table->foreignId('patient_id')->nullable()->constrained();
            $table->foreignId('appointment_id')->nullable()->constrained();

            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            $table->decimal('sub_total', 15, 2)->default(0);

            // Direct tax columns
            $table->decimal('total_cgst', 15, 2)->default(0);
            $table->decimal('total_sgst', 15, 2)->default(0);
            $table->decimal('total_igst', 15, 2)->default(0);

            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);

            $table->enum('status', ['draft','sent','paid','cancelled'])
                ->default('draft');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
