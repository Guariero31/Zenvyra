<?php

declare(strict_types=1);

namespace Tests\Feature;

use Filament\Facades\Filament;
use Tests\TestCase;

class StorePanelTenantMenuTest extends TestCase
{
    public function test_store_panel_does_not_show_the_tenant_switcher(): void
    {
        $this->assertFalse(Filament::getPanel('app')->hasTenantMenu());
    }
}
