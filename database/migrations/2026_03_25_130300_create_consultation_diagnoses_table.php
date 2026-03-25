<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultations')->cascadeOnDelete();
            $table->foreignId('icd10_code_id')->nullable()->constrained('icd10_codes')->nullOnDelete();
            $table->string('diagnosis_name');
            $table->string('icd10_code', 40)->nullable();
            $table->string('diagnosis_type', 30)->default('provisional');
            $table->string('clinical_status', 30)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_diagnoses');
    }
};
