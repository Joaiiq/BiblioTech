<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EmprestimoSolicitado extends Notification
{
    use Queueable;

    protected $emprestimo;

    public function __construct($emprestimo)
    {
        $this->emprestimo = $emprestimo;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $titulo = $this->emprestimo->livro?->titulo ?? 'livro';
        $membro = $this->emprestimo->membro;
        $nomeMembro = $membro?->nome ?? 'Membro não identificado';
        $identificacao = $membro?->numero_carteirinha ?: $membro?->email;

        return [
            'type' => 'emprestimo_solicitado',
            'emprestimo_id' => $this->emprestimo->id ?? null,
            'membro_id' => $membro?->id,
            'membro_nome' => $nomeMembro,
            'membro_identificacao' => $identificacao,
            'title' => 'Novo pedido de aluguel',
            'message' => "O membro {$nomeMembro} solicitou o aluguel do livro '{$titulo}'.",
        ];
    }
}
