<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variations', function (Blueprint $table): void {
            // Null price means "use the parent product's price" — an
            // override, not a required field.
            $table->decimal('price', 10, 2)->nullable()->after('stock');
            $table->integer('low_stock_threshold')->default(5)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table): void {
            $table->dropColumn(['price', 'low_stock_threshold']);
        });
    }
};
