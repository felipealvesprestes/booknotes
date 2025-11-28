<?php

use App\Mail\ReengagementEmail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

test('reengagement email envelope contains the expected subject', function (): void {
    $mailable = new ReengagementEmail(null, 'https://booknotes.com.br', 'https://instagram.com/booknotes', 'booknotes@example.com');

    $envelope = $mailable->envelope();

    expect($envelope)->toBeInstanceOf(Envelope::class)
        ->and($envelope->subject)->toBe('Tem novidades no Booknotes esperando por você');
});

test('reengagement email provides the correct view data', function (): void {
    $mailable = new ReengagementEmail(
        'Gabi',
        'https://booknotes.com.br',
        'https://instagram.com/booknotes',
        'booknotes@example.com'
    );

    $content = $mailable->content();

    expect($content)->toBeInstanceOf(Content::class)
        ->and($content->view)->toBe('emails.users.reengagement')
        ->and($content->with)->toMatchArray([
            'recipientName' => 'Gabi',
            'booknotesUrl' => 'https://booknotes.com.br',
            'instagramUrl' => 'https://instagram.com/booknotes',
            'supportEmail' => 'booknotes@example.com',
        ]);
});

test('reengagement email does not attach files', function (): void {
    $mailable = new ReengagementEmail(
        'Gabi',
        'https://booknotes.com.br',
        'https://instagram.com/booknotes',
        'booknotes@example.com'
    );

    expect($mailable->attachments())->toBe([]);
});
