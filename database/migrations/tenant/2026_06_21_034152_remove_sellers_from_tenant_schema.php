<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'seller_id')) {
            Schema::dropIfExists('sellers');

            return;
        }

        DB::table('orders')
            ->whereNotNull('seller_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $order): void {
                $userId = DB::table('sellers')
                    ->where('id', $order->seller_id)
                    ->value('user_id');

                if ($userId !== null && blank($order->user_id)) {
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['user_id' => $userId]);
                }
            });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_id');
        });

        Schema::dropIfExists('sellers');
    }

    public function down(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('seller_id')
                ->nullable()
                ->after('user_id')
                ->constrained('sellers')
                ->nullOnDelete();
        });
    }
};
