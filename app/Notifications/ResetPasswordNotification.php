<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset Kata Sandi — Helpdesk Enterprise')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Kami menerima permintaan untuk mereset kata sandi akun Helpdesk Anda.')
            ->action('Reset Kata Sandi', $url)
            ->line('Tautan ini berlaku selama 60 menit.')
            ->line('Jika Anda tidak meminta reset kata sandi, abaikan email ini — kata sandi Anda tidak akan berubah.');
    }
}
