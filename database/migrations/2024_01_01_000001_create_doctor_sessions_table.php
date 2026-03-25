<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('doctor_id');
            $table->enum('session_type', ['morning', 'afternoon', 'evening', 'night']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('break_enabled')->default(false);
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->timestamps();

            $table->index('doctor_id');

            $table->foreign('doctor_id')
                  ->references('id')
                  ->on('doctors')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_sessions');
    }
};
