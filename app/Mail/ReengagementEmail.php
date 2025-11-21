<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReengagementEmail extends Mailable
{
    public function __construct(
        public ?string $recipientName,
        public string $booknotesUrl,
        public string $instagramUrl,
        public string $supportEmail,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tem novidades no Booknotes esperando por você',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.users.reengagement',
            with: [
                'recipientName' => $this->recipientName,
                'booknotesUrl' => $this->booknotesUrl,
                'instagramUrl' => $this->instagramUrl,
                'supportEmail' => $this->supportEmail,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
