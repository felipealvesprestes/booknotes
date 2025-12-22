<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InstitutionalPartnershipEmail extends Mailable
{
    public function __construct(
        public string $institutionName,
        public string $booknotesUrl,
        public string $contactEmail,
        public string $contactPhone,
        public string $contactName = 'Felipe Prestes',
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Proposta de parceria educacional (piloto) – Plataforma Booknotes',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.institutions.partnership',
            with: [
                'institutionName' => $this->institutionName,
                'booknotesUrl' => $this->booknotesUrl,
                'contactEmail' => $this->contactEmail,
                'contactPhone' => $this->contactPhone,
                'contactName' => $this->contactName,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
