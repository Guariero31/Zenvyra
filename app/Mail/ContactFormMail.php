<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Enviado ao lojista quando um visitante preenche o formulário de contato
 * do site público da loja.
 */
class ContactFormMail extends Mailable
{
    /**
     * @param  string  $storeName  Nome de exibição da loja
     * @param  string  $senderName  Nome informado pelo visitante
     * @param  string  $senderEmail  E-mail informado pelo visitante
     * @param  string  $message  Mensagem enviada pelo visitante
     */
    public function __construct(
        public readonly string $storeName,
        public readonly string $senderName,
        public readonly string $senderEmail,
        public readonly string $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nova mensagem de contato via site — {$this->storeName}",
            replyTo: [$this->senderEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-form',
            with: [
                'subject' => "Nova mensagem de contato via site — {$this->storeName}",
                'storeName' => $this->storeName,
                'senderName' => $this->senderName,
                'senderEmail' => $this->senderEmail,
                'message' => $this->message,
            ],
        );
    }
}
