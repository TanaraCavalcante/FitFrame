<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public readonly string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('backend.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset Password — Gestione FitFrame')
            ->line('Hai ricevuto questa email perché abbiamo ricevuto una richiesta di reset password per il tuo account.')
            ->action('Reimposta Password', $url)
            ->line('Se non hai richiesto un reset della password, ignora questa email.');
    }
}
