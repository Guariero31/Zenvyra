<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('platform_settings');
    }

    public function down(): void
    {
        // platform_settings was replaced by identidade_visual
    }
};
