<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseVerifyEmail
{
    /**
     * Build the mail representation of the message.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirme seu e-mail para liberar o Booknotes')
            ->view('emails.auth.verify-email', [
                'verifyUrl' => $this->verificationUrl($notifiable),
                'user' => $notifiable,
                'supportEmail' => config('mail.from.address') ?? config('mail.reply_to.address') ?? 'contato@booknotes.com.br',
            ]);
    }
}
