<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Enviado ao administrador da loja logo após ser vinculado como
 * AdministradorCliente de um novo tenant.
 */
class WelcomeMail extends Mailable
{
    /**
     * @param  string  $userName   Nome do usuário destinatário
     * @param  string  $storeName  Nome de exibição da loja
     * @param  string  $panelUrl   URL do painel da loja (/{slug})
     */
    public function __construct(
        public readonly string $userName,
        public readonly string $storeName,
        public readonly string $panelUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bem-vindo ao {$this->storeName} — sua loja está pronta!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'subject'   => "Bem-vindo ao {$this->storeName} — sua loja está pronta!",
                'userName'  => $this->userName,
                'storeName' => $this->storeName,
                'panelUrl'  => $this->panelUrl,
            ],
        );
    }
}
