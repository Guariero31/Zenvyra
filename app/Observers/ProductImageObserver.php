<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ProductImage;

class ProductImageObserver
{
    /**
     * A primeira imagem de um produto vira a capa automaticamente.
     */
    public function creating(ProductImage $image): void
    {
        if (! ProductImage::query()->where('product_id', $image->product_id)->exists()) {
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
     * Ao remover a capa, promove a imagem mais antiga restante a capa.
     */
    public function deleted(ProductImage $image): void
    {
        if (! $image->is_primary) {
            return;
        }

        $replacement = ProductImage::query()
            ->where('product_id', $image->product_id)
            ->oldest('id')
            ->first();

        $replacement?->update(['is_primary' => true]);
    }

    private function unsetOtherPrimaries(ProductImage $image): void
    {
        ProductImage::query()
            ->where('product_id', $image->product_id)
            ->where('id', '!=', $image->getKey())
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }
}
