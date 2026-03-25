<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_non_practice_days', function (Blueprint $table) {
            $table->id();
            $table->integer('doctor_id');
            $table->date('marked_date');
            $table->enum('type', ['holiday', 'non_practice']);
            $table->timestamps();

            $table->unique(['doctor_id', 'marked_date']);

            $table->foreign('doctor_id')
                  ->references('id')
                  ->on('doctors')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_non_practice_days');
    }
};
