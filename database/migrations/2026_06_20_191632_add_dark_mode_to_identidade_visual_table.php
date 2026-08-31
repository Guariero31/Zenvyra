<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identidade_visual', function (Blueprint $table): void {
            // Dark mode colors
            $table->string('cor_primaria_dark', 20)->default('#818CF8')->after('cor_erro');
            $table->string('cor_sucesso_dark', 20)->default('#34D399')->after('cor_primaria_dark');
            $table->string('cor_alerta_dark', 20)->default('#FCD34D')->after('cor_sucesso_dark');
            $table->string('cor_erro_dark', 20)->default('#FB7185')->after('cor_alerta_dark');

            // Background overrides — light
            $table->string('bg_page_light', 20)->nullable()->after('cor_erro_dark');
            $table->string('bg_sidebar_light', 20)->nullable()->after('bg_page_light');
            $table->string('bg_topbar_light', 20)->nullable()->after('bg_sidebar_light');

            // Background overrides — dark
            $table->string('bg_page_dark', 20)->nullable()->after('bg_topbar_light');
            $table->string('bg_sidebar_dark', 20)->nullable()->after('bg_page_dark');
            $table->string('bg_topbar_dark', 20)->nullable()->after('bg_sidebar_dark');
        });
    }

    public function down(): void
    {
        Schema::table('identidade_visual', function (Blueprint $table): void {
            $table->dropColumn([
                'cor_primaria_dark', 'cor_sucesso_dark', 'cor_alerta_dark', 'cor_erro_dark',
                'bg_page_light', 'bg_sidebar_light', 'bg_topbar_light',
                'bg_page_dark', 'bg_sidebar_dark', 'bg_topbar_dark',
            ]);
        });
    }
};
