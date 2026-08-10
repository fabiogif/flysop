<?php

namespace App\Notifications;

use App\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(public UserInvitation $invitation)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('invitations.accept.show', $this->invitation->token);
        $org = $this->invitation->tenant?->name ?? config('app.name');

        return (new MailMessage)
            ->subject("Convite para {$org}")
            ->greeting("Olá, {$this->invitation->name}")
            ->line("Você foi convidado(a) a participar de {$org} no " . config('app.name') . '.')
            ->action('Aceitar convite', $url)
            ->line('Este convite expira em ' . $this->invitation->expires_at->format('d/m/Y H:i') . '.')
            ->line('Se você não esperava este e-mail, ignore-o.');
    }
}
