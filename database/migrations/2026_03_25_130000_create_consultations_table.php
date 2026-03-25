<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            // Legacy tables in this project do not use consistent key types,
            // so keep these as indexed reference columns instead of hard FKs.
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->integer('doctor_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('source', 30)->default('patient');
            $table->string('status', 20)->default('draft');
            $table->date('visit_date')->nullable();
            $table->string('visit_time', 20)->nullable();
            $table->string('token_number', 50)->nullable();
            $table->string('chief_complaint_duration_value', 30)->nullable();
            $table->string('chief_complaint_duration_unit', 20)->nullable();
            $table->text('history_of_present_illness')->nullable();
            $table->json('chief_complaints')->nullable();
            $table->json('aggravating_factors')->nullable();
            $table->json('relieving_factors')->nullable();
            $table->json('associated_symptoms')->nullable();
            $table->string('general_appearance', 30)->nullable();
            $table->string('follow_up_label', 50)->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('referral_department')->nullable();
            $table->text('referral_note')->nullable();
            $table->text('investigation_instructions')->nullable();
            $table->text('advice')->nullable();
            $table->text('doctor_note')->nullable();
            $table->string('bp_systolic', 20)->nullable();
            $table->string('bp_diastolic', 20)->nullable();
            $table->string('heart_rate', 20)->nullable();
            $table->string('spo2', 20)->nullable();
            $table->string('temperature', 20)->nullable();
            $table->string('weight', 20)->nullable();
            $table->string('height', 20)->nullable();
            $table->string('bmi', 20)->nullable();
            $table->string('respiratory_rate', 20)->nullable();
            $table->string('grbs', 20)->nullable();
            $table->string('waist_circumference', 20)->nullable();
            $table->string('pain_score', 20)->nullable();
            $table->string('gcs', 20)->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique('payment_id');
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('created_by');
            $table->index(['patient_id', 'visit_date']);
            $table->index(['doctor_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
