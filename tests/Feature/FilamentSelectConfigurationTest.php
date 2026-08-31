<?php

declare(strict_types=1);

namespace Tests\Feature;

use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;
use Tests\TestCase;

class FilamentSelectConfigurationTest extends TestCase
{
    public function test_form_select_defaults_to_non_native(): void
    {
        $this->assertFalse(Select::make('status')->isNative());
    }

    public function test_table_select_filter_defaults_to_non_native(): void
    {
        $this->assertFalse(SelectFilter::make('status')->isNative());
    }
}
