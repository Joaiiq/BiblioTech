<?php

namespace App\Notifications;

use App\Models\Pagamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PagamentoRegistrado extends Notification
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
        $this->pagamento->loadMissing(['membro', 'emprestimo.livro']);

        return [
            'type' => 'pagamento_registrado',
            'pagamento_id' => $this->pagamento->id,
            'emprestimo_id' => $this->pagamento->emprestimo_id,
            'title' => 'Pagamento aguardando conferência',
            'message' => "{$this->pagamento->membro?->nome} enviou pagamento de R$ "
                . number_format((float) $this->pagamento->valor, 2, ',', '.')
                . " referente ao livro '{$this->pagamento->emprestimo?->livro?->titulo}'.",
        ];
    }
}
