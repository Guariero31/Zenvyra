<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('te_tenants', function (Blueprint $table) {
            $table->timestamp('te_data_retention_ends_at')->nullable()->after('te_ativo');
            $table->timestamp('te_data_purged_at')->nullable()->after('te_data_retention_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('te_tenants', function (Blueprint $table) {
            $table->dropColumn(['te_data_retention_ends_at', 'te_data_purged_at']);
        });
    }
};
