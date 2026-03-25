<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_slot_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('doctor_id');
            $table->unsignedSmallInteger('slot_duration')->default(15);
            $table->unsignedSmallInteger('advance_booking_days')->default(120);
            $table->boolean('slots_private')->default(false);
            $table->timestamps();

            $table->unique('doctor_id');

            $table->foreign('doctor_id')
                  ->references('id')
                  ->on('doctors')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_slot_settings');
    }
};
