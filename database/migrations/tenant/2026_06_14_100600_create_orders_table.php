<?php

use App\Enums\OrderOrigin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            // O vendedor vive no schema central (public.users); por isso a coluna
            // guarda apenas o identificador, sem foreign key entre schemas.
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('origin')->default(OrderOrigin::Balcao->value);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
