<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('notification_key')->unique();
            $table->string('template_key', 80);
            $table->string('dlt_template_id', 30);
            $table->string('mobile', 20);
            $table->text('message');
            $table->string('status', 20);
            $table->text('provider_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_notification_logs');
    }
};
