<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('grace_ends_at')->nullable()->after('ends_at');
            $table->timestamp('next_billing_at')->nullable()->after('grace_ends_at');
            $table->timestamp('canceled_at')->nullable()->after('next_billing_at');

            // Pontos de extensão para a Fase 3 (cobrança automática via gateway).
            // Preenchidos manualmente como nulos hoje; alimentados por webhook depois.
            $table->string('gateway')->nullable()->after('canceled_at');
            $table->string('gateway_subscription_id')->nullable()->after('gateway');
            $table->string('gateway_customer_id')->nullable()->after('gateway_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'grace_ends_at',
                'next_billing_at',
                'canceled_at',
                'gateway',
                'gateway_subscription_id',
                'gateway_customer_id',
            ]);
        });
    }
};
