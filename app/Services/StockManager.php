<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza toda mutação de saldo de estoque e mantém o histórico em
 * stock_movements. Produtos com variações controlam o saldo na variação;
 * produtos simples controlam direto em products.stock.
 */
class StockManager
{
    /**
     * Registra uma entrada manual de estoque (ex.: compra de fornecedor).
     */
    public function entry(Product $product, ?ProductVariation $variation, int $quantity, ?string $reason, ?int $userId): StockMovement
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'A quantidade da entrada deve ser maior que zero.',
            ]);
        }

        return DB::transaction(function () use ($product, $variation, $quantity, $reason, $userId): StockMovement {
            $this->applyDelta($product, $variation, $quantity);

            return $this->record(
                productId: $variation?->product_id ?? $product->getKey(),
                variationId: $variation?->getKey(),
                type: StockMovementType::Entry,
                quantity: $quantity,
                orderId: null,
                reason: $reason,
                userId: $userId,
            );
        });
    }

    /**
     * Baixa o estoque por causa de uma venda e registra o movimento.
     * Deve ser chamado dentro da transação do pedido.
     */
    public function registerSale(OrderItem $item): void
    {
        $this->applyDelta($item->product, $item->variation, -$item->quantity);

        $this->record(
            productId: $item->product_id,
            variationId: $item->product_variation_id,
            type: StockMovementType::Sale,
            quantity: $item->quantity,
            orderId: $item->order_id,
            reason: null,
            userId: null,
        );
    }

    /**
     * Registra um ajuste manual de estoque (positivo = entrada, negativo = baixa).
     */
    public function adjustment(Product $product, ?ProductVariation $variation, int $signedQuantity, ?string $reason, ?int $userId): StockMovement
    {
        if ($signedQuantity === 0) {
            throw ValidationException::withMessages([
                'quantity' => 'O ajuste não pode ser zero.',
            ]);
        }

        return DB::transaction(function () use ($product, $variation, $signedQuantity, $reason, $userId): StockMovement {
            $this->applyDelta($product, $variation, $signedQuantity);

            return $this->record(
                productId: $variation?->product_id ?? $product->getKey(),
                variationId: $variation?->getKey(),
                type: StockMovementType::Adjustment,
                quantity: $signedQuantity,
                orderId: null,
                reason: $reason,
                userId: $userId,
            );
        });
    }

    /**
     * Devolve estoque por causa de um estorno e registra o movimento.
     * Deve ser chamado dentro da transação do estorno.
     */
    public function registerRefund(OrderItem $item, int $quantity, ?int $userId): void
    {
        $this->applyDelta($item->product, $item->variation, $quantity);

        $this->record(
            productId: $item->product_id,
            variationId: $item->product_variation_id,
            type: StockMovementType::Refund,
            quantity: $quantity,
            orderId: $item->order_id,
            reason: null,
            userId: $userId,
        );
    }

    private function applyDelta(?Product $product, ?ProductVariation $variation, int $signedQuantity): void
    {
        $target = $variation ?? $product;

        if (! $target || $signedQuantity === 0) {
            return;
        }

        if ($signedQuantity > 0) {
            $target->increment('stock', $signedQuantity);

            return;
        }

        $quantidade = abs($signedQuantity);

        if (! $this->permiteEstoqueNegativo() && (int) $target->stock < $quantidade) {
            throw ValidationException::withMessages([
                'quantity' => 'Estoque insuficiente. Esta loja não permite saldo negativo.',
            ]);
        }

        $target->decrement('stock', $quantidade);
    }

    private function permiteEstoqueNegativo(): bool
    {
        $tenant = TenantContext::current();

        return $tenant instanceof Tenant
            ? $tenant->gestaoSettings()->permitirEstoqueNegativo()
            : false;
    }

    private function record(int $productId, ?int $variationId, StockMovementType $type, int $quantity, ?int $orderId, ?string $reason, ?int $userId): StockMovement
    {
        return StockMovement::create([
            'product_id' => $productId,
            'product_variation_id' => $variationId,
            'order_id' => $orderId,
            'type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
            'user_id' => $userId,
        ]);
    }
}
