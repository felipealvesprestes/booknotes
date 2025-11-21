<?php

use App\Mail\ReengagementEmail;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('email:reengagement {emails?}', function (?string $emails = null) {
    $rawEmails = $emails ?? config('services.booknotes.reengagement_emails', []);

    $emailList = collect(is_string($rawEmails) ? explode(',', $rawEmails) : $rawEmails)
        ->map(fn ($address) => strtolower(trim($address)))
        ->filter(fn ($address) => filter_var($address, FILTER_VALIDATE_EMAIL))
        ->unique()
        ->values();

    if ($emailList->isEmpty()) {
        $this->warn('Nenhum e-mail válido encontrado. Preencha REENGAGEMENT_EMAILS no .env ou informe via argumento.');

        return;
    }

    $booknotesUrl = rtrim(config('services.booknotes.booknotes_url', 'https://booknotes.com.br'), '/');
    $instagramUrl = config('services.booknotes.instagram_url', 'https://instagram.com/booknotes.br');
    $supportEmail = config('mail.from.address') ?? config('mail.reply_to.address') ?? 'contato@booknotes.com.br';

    foreach ($emailList as $address) {
        $user = User::where('email', $address)->first();

        Mail::to($address)->send(new ReengagementEmail(
            recipientName: $user?->name,
            booknotesUrl: $booknotesUrl,
            instagramUrl: $instagramUrl,
            supportEmail: $supportEmail,
        ));

        $this->info(sprintf(
            'E-mail enviado para %s%s',
            $address,
            $user?->name ? " ({$user->name})" : ''
        ));
    }

    $this->info('Envio de reengajamento concluído.');
})->purpose('Enviar o e-mail de retorno para usuários inativos');
