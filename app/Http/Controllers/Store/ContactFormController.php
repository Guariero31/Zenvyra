<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Enums\TenantPortalRole;
use App\Mail\ContactFormMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactFormController extends StoreController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $storeName = $this->theme->nome_loja ?? $this->tenant->te_name;

        // Recipient: store_config email, or first AdministradorCliente user
        $recipientEmail = $this->theme->storeConfig('email')
            ?? $this->tenant->users()
                ->wherePivot('tu_papel', TenantPortalRole::AdministradorCliente->value)
                ->value('email');

        if ($recipientEmail) {
            Mail::to($recipientEmail)->send(new ContactFormMail(
                storeName:   $storeName,
                senderName:  $data['name'],
                senderEmail: $data['email'],
                message:     $data['message'],
            ));
        }

        return redirect()
            ->route('store.contact', $this->tenant->te_slug)
            ->with('contact_success', true);
    }
}
