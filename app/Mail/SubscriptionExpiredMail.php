<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Enviado aos administradores da loja imediatamente após a assinatura expirar.
 * Disparado em SubscriptionManager::expire().
 */
class SubscriptionExpiredMail extends Mailable
{
    /**
     * @param  string  $storeName  Nome de exibição da loja
     * @param  string  $planName  Nome do plano expirado
     * @param  string  $expiredAt  Data de expiração formatada (d/m/Y)
     */
    public function __construct(
        public readonly string $storeName,
        public readonly string $planName,
        public readonly string $expiredAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Assinatura expirada — {$this->storeName}");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-expired',
            with: [
                'subject' => "Assinatura expirada — {$this->storeName}",
                'storeName' => $this->storeName,
                'planName' => $this->planName,
                'expiredAt' => $this->expiredAt,
            ],
        );
    }
}
