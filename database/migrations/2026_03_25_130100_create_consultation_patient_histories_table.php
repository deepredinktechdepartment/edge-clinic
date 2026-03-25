<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_patient_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->json('past_medical_history')->nullable();
            $table->json('surgical_history')->nullable();
            $table->json('family_history')->nullable();
            $table->json('drug_allergies')->nullable();
            $table->json('chronic_conditions')->nullable();
            $table->json('ongoing_medications')->nullable();
            $table->timestamps();

            $table->unique('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_patient_histories');
    }
};
