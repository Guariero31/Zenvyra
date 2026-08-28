<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Enviado ao cliente final quando o pedido muda para o status "paid".
 * Disparado em OrderObserver::updated().
 *
 * @phpstan-type OrderItemData array{name: string, variation: string|null, quantity: int, subtotal: string}
 */
class OrderPaidMail extends Mailable
{
    /**
     * @param  string  $customerName  Nome do cliente
     * @param  int  $orderId  ID do pedido
     * @param  string  $storeName  Nome de exibição da loja
     * @param  string|null  $paymentMethod  Forma de pagamento (ex.: "Cartão de crédito")
     * @param  array<OrderItemData>  $items  Itens do pedido
     * @param  string  $totalAmount  Total do pedido (decimal string)
     */
    public function __construct(
        public readonly string $customerName,
        public readonly int $orderId,
        public readonly string $storeName,
        public readonly ?string $paymentMethod,
        public readonly array $items,
        public readonly string $totalAmount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Pedido #{$this->orderId} confirmado — {$this->storeName}");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-paid',
            with: [
                'subject' => "Pedido #{$this->orderId} confirmado — {$this->storeName}",
                'customerName' => $this->customerName,
                'orderId' => $this->orderId,
                'storeName' => $this->storeName,
                'paymentMethod' => $this->paymentMethod,
                'items' => $this->items,
                'totalAmount' => $this->totalAmount,
            ],
        );
    }
}
