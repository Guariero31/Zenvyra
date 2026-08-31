<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use Illuminate\Contracts\View\View;

class ContactController extends StoreController
{
    public function __invoke(): View
    {
        $config = $this->theme->store_config ?? [];

        return view('store.contact', array_merge($this->shared(), [
            'whatsapp' => $config['whatsapp'] ?? null,
            'email' => $config['email'] ?? null,
            'endereco' => $config['endereco'] ?? null,
        ]));
    }
}
