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
       Schema::create('medicines', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('name', 255);
            $table->decimal('price_inr', 10, 2)->nullable();
            $table->boolean('is_discontinued')->default(false);
            $table->string('manufacturer_name', 255)->nullable();
            $table->string('type', 100)->nullable();
            $table->string('pack_size_label', 255)->nullable();
            $table->string('short_composition1', 255)->nullable();
            $table->string('short_composition2', 255)->nullable();

            // Substitutes
            $table->string('substitute0', 255)->nullable();
            $table->string('substitute1', 255)->nullable();
            $table->string('substitute2', 255)->nullable();
            $table->string('substitute3', 255)->nullable();
            $table->string('substitute4', 255)->nullable();

            // Side Effects
            $table->text('consolidated_side_effects')->nullable();

            // Uses
            $table->string('use0', 255)->nullable();
            $table->string('use1', 255)->nullable();
            $table->string('use2', 255)->nullable();
            $table->string('use3', 255)->nullable();
            $table->string('use4', 255)->nullable();

            // Classification
            $table->string('chemical_class', 255)->nullable();
            $table->boolean('habit_forming')->default(false);
            $table->string('therapeutic_class', 255)->nullable();
            $table->string('action_class', 255)->nullable();

            $table->timestamps();

            // Useful indexes
            $table->index('name');
            $table->index('manufacturer_name');
            $table->index('therapeutic_class');
            $table->index('is_discontinued');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
