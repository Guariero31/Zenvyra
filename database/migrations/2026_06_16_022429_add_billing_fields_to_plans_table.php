<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('currency', 3)->default('BRL')->after('price');
            $table->unsignedInteger('trial_days')->default(0)->after('billing_period');
            $table->unsignedInteger('max_products')->nullable()->after('trial_days');
            $table->unsignedInteger('max_users')->nullable()->after('max_products');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['currency', 'trial_days', 'max_products', 'max_users']);
        });
    }
};
