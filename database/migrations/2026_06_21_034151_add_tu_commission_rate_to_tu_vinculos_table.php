<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tu_vinculos', function (Blueprint $table) {
            $table->decimal('tu_commission_rate', 5, 2)
                ->default(0)
                ->after('tu_rotulo_setor');
        });
    }

    public function down(): void
    {
        Schema::table('tu_vinculos', function (Blueprint $table) {
            $table->dropColumn('tu_commission_rate');
        });
    }
};
