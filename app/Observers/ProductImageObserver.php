<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Builder;

class ProductImageObserver
{
    /**
     * A primeira imagem do produto (ou da variação) vira a capa
     * automaticamente. Produto e cada variação têm seu próprio conjunto de
     * capa, isolado por product_variation_id.
     */
    public function creating(ProductImage $image): void
    {
        if (! $this->scoped($image)->exists()) {
            $image->is_primary = true;
        }
    }

    public function saved(ProductImage $image): void
    {
        if ($image->is_primary) {
            $this->unsetOtherPrimaries($image);
        }
    }

    /**
     * Ao remover a capa, promove a imagem mais antiga restante (no mesmo
     * escopo) a capa.
     */
    public function deleted(ProductImage $image): void
    {
        if (! $image->is_primary) {
            return;
        }

        $replacement = $this->scoped($image)->oldest('id')->first();

        $replacement?->update(['is_primary' => true]);
    }

    private function unsetOtherPrimaries(ProductImage $image): void
    {
        $this->scoped($image)
            ->where('id', '!=', $image->getKey())
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }

    /** Query escopada ao mesmo produto e à mesma variação (ou ausência dela). */
    private function scoped(ProductImage $image): Builder
    {
        return ProductImage::query()
            ->where('product_id', $image->product_id)
            ->where('product_variation_id', $image->product_variation_id);
    }
}
