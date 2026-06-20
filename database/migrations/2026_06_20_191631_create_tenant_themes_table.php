<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_themes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();

            $table->string('nome_loja', 80)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();

            // Light mode
            $table->string('cor_primaria_light', 20)->default('#4F46E5');
            $table->string('cor_sucesso_light', 20)->default('#10B981');
            $table->string('cor_alerta_light', 20)->default('#F59E0B');
            $table->string('cor_erro_light', 20)->default('#F43F5E');
            $table->string('bg_page_light', 20)->nullable();
            $table->string('bg_sidebar_light', 20)->nullable();
            $table->string('bg_topbar_light', 20)->nullable();

            // Dark mode
            $table->string('cor_primaria_dark', 20)->default('#818CF8');
            $table->string('cor_sucesso_dark', 20)->default('#34D399');
            $table->string('cor_alerta_dark', 20)->default('#FCD34D');
            $table->string('cor_erro_dark', 20)->default('#FB7185');
            $table->string('bg_page_dark', 20)->nullable();
            $table->string('bg_sidebar_dark', 20)->nullable();
            $table->string('bg_topbar_dark', 20)->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('te_tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_themes');
    }
};
