<?php

declare(strict_types=1);

namespace App\Support;

class ColorNormalizer
{
    /**
     * Converts any CSS color string to a 7-char hex (#RRGGBB).
     *
     * Accepts: #RRGGBB, rgba(r,g,b,a), rgb(r,g,b).
     * Returns the input unchanged when the format is unrecognized.
     */
    public static function hexFromAny(string $valor): string
    {
        $valor = trim($valor);

        if (preg_match('/^#[0-9a-fA-F]{6}$/', $valor)) {
            return $valor;
        }

        if (preg_match('/rgba?\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i', $valor, $matches)) {
            return sprintf('#%02x%02x%02x', (int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        return $valor;
    }
}
