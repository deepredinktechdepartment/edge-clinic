<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('partner_webhook_delivery_logs')) {
            Schema::create('partner_webhook_delivery_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('partner_webhook_integration_id');
                $table->unsignedBigInteger('payment_id')->nullable()->index();
                $table->unsignedBigInteger('appointment_id')->nullable()->index();
                $table->string('event', 30);
                $table->json('payload');
                $table->unsignedSmallInteger('response_status')->nullable();
                $table->text('response_body')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('partner_webhook_delivery_logs', function (Blueprint $table) {
            $table->foreign('partner_webhook_integration_id', 'pwh_delivery_log_integration_fk')
                ->references('id')
                ->on('partner_webhook_integrations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_webhook_delivery_logs');
    }
};
