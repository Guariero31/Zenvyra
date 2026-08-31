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
        Schema::table('te_tenants', function (Blueprint $table) {
            $table->string('brand_name')->nullable()->after('te_name');
            $table->string('brand_primary_color', 7)->nullable()->after('brand_name');
            $table->string('brand_logo_path')->nullable()->after('brand_primary_color');
        });
    }

    public function down(): void
    {
        Schema::table('te_tenants', function (Blueprint $table) {
            $table->dropColumn(['brand_name', 'brand_primary_color', 'brand_logo_path']);
        });
    }
};
