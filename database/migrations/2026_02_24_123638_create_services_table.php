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
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();

            $table->unsignedBigInteger('parent_id')->nullable();

            // 💰 Pricing
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 10)->default('INR');

            // Billing
            $table->enum('billing_type', ['billable', 'non_billable'])->nullable();

            // GST
            $table->boolean('gst_applicable')->default(0);
            $table->decimal('gst_percentage', 5, 2)->nullable();

            $table->text('service_terms')->nullable();

            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('services')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
