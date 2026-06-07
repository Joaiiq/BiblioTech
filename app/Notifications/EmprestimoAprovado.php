<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EmprestimoAprovado extends Notification
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
        $limiteRetirada = $this->emprestimo->data_limite_retirada?->format('d/m/Y');

        return [
            'type' => 'emprestimo_aprovado',
            'emprestimo_id' => $this->emprestimo->id ?? null,
            'title' => 'Empréstimo aprovado',
            'message' => $limiteRetirada
                ? "Seu pedido do livro '{$titulo}' foi aprovado. Retire presencialmente até {$limiteRetirada}."
                : "Seu pedido de empréstimo do livro '{$titulo}' foi aprovado.",
        ];
    }
}
