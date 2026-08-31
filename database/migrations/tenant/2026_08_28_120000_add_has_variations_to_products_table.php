<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('has_variations')->default(false)->after('status');
        });

        // Backfill: products that already have variation rows get the flag
        // turned on, so existing data keeps working under the new toggle.
        DB::table('products')
            ->whereIn('id', function ($query): void {
                $query->select('product_id')->from('product_variations')->distinct();
            })
            ->update(['has_variations' => true]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('has_variations');
        });
    }
};
