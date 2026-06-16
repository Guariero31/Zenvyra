<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('te_tenants', function (Blueprint $table) {
            $table->id();
            $table->string('te_name');
            $table->string('te_slug')->unique();
            $table->string('te_schema')->unique();
            $table->boolean('te_ativo')->default(true);
            $table->json('te_gestao_settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('te_tenants');
    }
};
