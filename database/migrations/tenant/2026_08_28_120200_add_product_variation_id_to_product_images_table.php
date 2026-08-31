<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table): void {
            // Null = image belongs to the parent product. Set = image
            // belongs to a specific variation instead.
            $table->foreignId('product_variation_id')
                ->nullable()
                ->after('product_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('product_variation_id');
        });
    }
};
