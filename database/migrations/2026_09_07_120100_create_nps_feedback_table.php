<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nps_feedback', function (Blueprint $table) {
            $table->id();
            // The legacy production tables do not guarantee matching FK column
            // types across deployments, so retain indexed references without
            // database-level constraints.
            $table->unsignedBigInteger('payment_id')->unique();
            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->unsignedTinyInteger('score');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nps_feedback');
    }
};
