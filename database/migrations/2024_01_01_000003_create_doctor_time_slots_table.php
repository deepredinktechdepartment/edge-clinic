<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_time_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('doctor_id');
            $table->tinyInteger('day_of_week')->comment('0=Sat,1=Sun,2=Mon,3=Tue,4=Wed,5=Thu,6=Fri');
            $table->time('slot_time');
            $table->enum('session_type', ['morning', 'afternoon', 'evening', 'night']);
            $table->boolean('is_reserved')->default(false);
            $table->boolean('is_weekly_off')->default(false);
            $table->timestamps();

            $table->index(['doctor_id', 'day_of_week']);

            $table->foreign('doctor_id')
                  ->references('id')
                  ->on('doctors')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_time_slots');
    }
};
