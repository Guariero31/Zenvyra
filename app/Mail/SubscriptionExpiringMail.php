<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Enviado aos administradores da loja quando a assinatura está próxima do vencimento.
 * Disparado pelo comando subscriptions:notify-expiring.
 */
class SubscriptionExpiringMail extends Mailable
{
    /**
     * @param  string  $storeName  Nome de exibição da loja
     * @param  string  $planName  Nome do plano atual
     * @param  int  $daysRemaining  Dias restantes até o vencimento
     * @param  string  $expiresAt  Data de vencimento formatada (d/m/Y)
     * @param  int  $graceDays  Dias de tolerância após o vencimento
     */
    public function __construct(
        public readonly string $storeName,
        public readonly string $planName,
        public readonly int $daysRemaining,
        public readonly string $expiresAt,
        public readonly int $graceDays,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->daysRemaining === 1 ? 'dia' : 'dias';

        return new Envelope(
            subject: "Sua assinatura vence em {$this->daysRemaining} {$label} — {$this->storeName}",
        );
    }

    public function content(): Content
    {
        $label = $this->daysRemaining === 1 ? 'dia' : 'dias';
        $subject = "Sua assinatura vence em {$this->daysRemaining} {$label} — {$this->storeName}";

        return new Content(
            view: 'emails.subscription-expiring',
            with: [
                'subject' => $subject,
                'storeName' => $this->storeName,
                'planName' => $this->planName,
                'daysRemaining' => $this->daysRemaining,
                'expiresAt' => $this->expiresAt,
                'graceDays' => $this->graceDays,
            ],
        );
    }
}
