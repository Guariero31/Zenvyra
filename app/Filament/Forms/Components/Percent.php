<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class Percent extends TextInput
{
    protected bool $treatEmptyAsNull = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->suffix('%')
            ->maxLength(6)
            ->extraAlpineAttributes([
                'x-on:keypress' => 'function() {
                    var charCode = event.keyCode || event.which;
                    if (charCode < 48 || charCode > 57) {
                        event.preventDefault();
                        return false;
                    }
                    return true;
                }',
                'x-on:keyup' => 'function() {
                    var digits = $el.value.replace(/\D/g, "");
                    if (digits === "") {
                        $el.value = "";
                        return;
                    }
                    var formatted = (parseInt(digits, 10) / 100).toFixed(2).replace(".", ",");
                    formatted = formatted.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
                    $el.value = formatted;
                }',
            ])
            ->formatStateUsing(fn ($state): ?string => $this->formatPercent($state))
            ->dehydrateStateUsing(fn ($state): ?float => $this->dehydratePercent($state));
    }

    public function nullable(bool|Closure $condition = true): static
    {
        $this->treatEmptyAsNull = true;

        return parent::nullable($condition);
    }

    private function formatPercent(mixed $state): ?string
    {
        if ($this->treatEmptyAsNull && blank($state)) {
            return null;
        }

        if (is_numeric($state)) {
            return number_format((float) $state, 2, ',', '.');
        }

        return filled($state) ? (string) $state : '0,00';
    }

    private function dehydratePercent(mixed $state): ?float
    {
        if ($this->treatEmptyAsNull && blank($state)) {
            return null;
        }

        if (is_numeric($state)) {
            return round((float) $state, 2);
        }

        $normalized = Str::of((string) $state)
            ->replace('.', '')
            ->replace(',', '.')
            ->toString();

        if ($normalized === '') {
            return $this->treatEmptyAsNull ? null : 0.0;
        }

        return round((float) $normalized, 2);
    }
}
