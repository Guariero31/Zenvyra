<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_themes', function (Blueprint $table): void {
            $table->jsonb('store_config')->nullable()->after('bg_topbar_dark');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_themes', function (Blueprint $table): void {
            $table->dropColumn('store_config');
        });
    }
};
