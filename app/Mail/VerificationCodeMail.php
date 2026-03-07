<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;

    /**
     * Create a new message instance.
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Sujet du mail
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre code de vérification BHDM',
        );
    }

    /**
     * Vue utilisée pour le mail
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-code',
            with: [
                'code' => $this->code,
            ],
        );
    }

    /**
     * Pièces jointes
     */
    public function attachments(): array
    {
        return [];
    }
}
