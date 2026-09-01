<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_webhook_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->unique()->constrained('sources')->cascadeOnDelete();
            $table->string('partner_name', 100);
            $table->boolean('is_enabled')->default(false);
            $table->string('webhook_url', 2048);
            $table->string('basic_auth_username', 255)->nullable();
            $table->text('basic_auth_password')->nullable();
            $table->unsignedSmallInteger('timeout_seconds')->default(15);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_webhook_integrations');
    }
};
