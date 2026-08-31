<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

/**
 * Filament's default CreateRecord redirects to the Edit page after saving,
 * when one exists. This project prefers going back to the list instead.
 */
trait RedirectsToIndexAfterCreate
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
