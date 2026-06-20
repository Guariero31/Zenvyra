<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identidade_visual', function (Blueprint $table): void {
            $table->id();
            $table->string('nome_sistema', 120)->default('Zenvyra · Master');
            $table->string('cor_primaria', 20)->default('#4F46E5');
            $table->string('cor_sucesso', 20)->default('#10B981');
            $table->string('cor_alerta', 20)->default('#F59E0B');
            $table->string('cor_erro', 20)->default('#F43F5E');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identidade_visual');
    }
};
