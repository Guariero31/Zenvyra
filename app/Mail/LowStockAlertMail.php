<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Enviado aos administradores da loja quando o estoque de um produto
 * atinge ou fica abaixo do limite mínimo configurado (low_stock_threshold).
 * Disparado em OrderObserver::updated() após a baixa do pedido.
 */
class LowStockAlertMail extends Mailable
{
    /**
     * @param  string  $storeName  Nome de exibição da loja
     * @param  string  $productName  Nome do produto
     * @param  string|null  $variationName  Nome da variação (null para produtos simples)
     * @param  int  $currentStock  Saldo atual após a baixa
     * @param  int  $threshold  Limite mínimo configurado
     */
    public function __construct(
        public readonly string $storeName,
        public readonly string $productName,
        public readonly ?string $variationName,
        public readonly int $currentStock,
        public readonly int $threshold,
    ) {}

    private function subjectLine(): string
    {
        $label = $this->variationName
            ? "{$this->productName} · {$this->variationName}"
            : $this->productName;

        return "Estoque crítico: {$label} — {$this->storeName}";
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine());
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.low-stock-alert',
            with: [
                'subject' => $this->subjectLine(),
                'storeName' => $this->storeName,
                'productName' => $this->productName,
                'variationName' => $this->variationName,
                'currentStock' => $this->currentStock,
                'threshold' => $this->threshold,
            ],
        );
    }
}
