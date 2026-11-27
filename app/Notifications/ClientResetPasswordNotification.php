<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientResetPasswordNotification extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('client.password.reset', ['token' => $this->token, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject('Redefinição de senha — ' . config('app.name'))
            ->greeting("Olá, {$notifiable->name}")
            ->line('Recebemos um pedido para redefinir a senha da sua conta no app.')
            ->action('Criar nova senha', $url)
            ->line('O link expira em ' . config('auth.passwords.clients.expire') . ' minutos.')
            ->line('Se você não pediu, ignore este e-mail: sua senha continua a mesma.');
    }
}
