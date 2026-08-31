<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Support\ColorNormalizer;

/**
 * Normalizes color columns to #RRGGBB format before each save.
 * Handles rgba()/rgb() values that Filament ColorPicker may produce.
 */
trait NormalizaCoresHex
{
    protected static function bootNormalizaCoresHex(): void
    {
        static::saving(static function (self $model): void {
            $camposCor = [
                'cor_primaria', 'cor_primaria_light', 'cor_primaria_dark',
                'cor_sucesso', 'cor_sucesso_light', 'cor_sucesso_dark',
                'cor_alerta', 'cor_alerta_light', 'cor_alerta_dark',
                'cor_erro', 'cor_erro_light', 'cor_erro_dark',
                'bg_page_light', 'bg_page_dark',
                'bg_content_light', 'bg_content_dark',
                'bg_sidebar_light', 'bg_sidebar_dark',
                'bg_topbar_light', 'bg_topbar_dark',
            ];

            foreach ($camposCor as $campo) {
                if (array_key_exists($campo, $model->getAttributes()) && filled($model->{$campo})) {
                    $model->{$campo} = ColorNormalizer::hexFromAny($model->{$campo});
                }
            }
        });
    }
}
