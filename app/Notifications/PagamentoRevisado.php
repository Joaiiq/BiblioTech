<?php

namespace App\Notifications;

use App\Models\Pagamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PagamentoRevisado extends Notification
{
    use Queueable;

    public function __construct(private readonly Pagamento $pagamento)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'pagamento_revisado',
            'pagamento_id' => $this->pagamento->id,
            'title' => $this->pagamento->status === Pagamento::STATUS_APROVADO
                ? 'Pagamento aprovado'
                : 'Pagamento recusado',
            'message' => $this->pagamento->status === Pagamento::STATUS_APROVADO
                ? 'Seu pagamento foi conferido pela equipe e a multa foi regularizada.'
                : 'Seu pagamento foi recusado pela equipe. Revise os dados e envie novamente.',
        ];
    }
}
