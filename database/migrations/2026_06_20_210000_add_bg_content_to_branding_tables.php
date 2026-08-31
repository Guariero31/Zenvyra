<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identidade_visual', function (Blueprint $table): void {
            $table->string('bg_content_light', 20)->nullable()->after('bg_page_light');
            $table->string('bg_content_dark', 20)->nullable()->after('bg_page_dark');
        });

        Schema::table('tenant_themes', function (Blueprint $table): void {
            $table->string('bg_content_light', 20)->nullable()->after('bg_page_light');
            $table->string('bg_content_dark', 20)->nullable()->after('bg_page_dark');
        });
    }

    public function down(): void
    {
        Schema::table('identidade_visual', function (Blueprint $table): void {
            $table->dropColumn(['bg_content_light', 'bg_content_dark']);
        });

        Schema::table('tenant_themes', function (Blueprint $table): void {
            $table->dropColumn(['bg_content_light', 'bg_content_dark']);
        });
    }
};
