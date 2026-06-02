<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EquipeOperacaoNotificada extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $type,
        private readonly string $title,
        private readonly string $message,
        private readonly array $payload = []
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return array_merge([
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
        ], $this->payload);
    }
}
