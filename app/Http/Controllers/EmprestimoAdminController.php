<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Emprestimos; // Usando o seu Model no plural!
use App\Models\Pagamento;
use App\Models\Reserva;
use App\Notifications\EmprestimoRejeitado;
use App\Notifications\EmprestimoAprovado;
use App\Notifications\EmprestimoRetirado;
use App\Notifications\EquipeOperacaoNotificada;
use App\Notifications\ReservaDisponivel;
use App\Models\User;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class EmprestimoAdminController extends Controller
{
    /**
     * Lista todos os empréstimos para o bibliotecário administrar
     */
    public function index()
    {
        Emprestimos::expirarRetiradasPendentes();

        // Traz os empréstimos e já carrega os relacionamentos (livro, e o user dentro de membro)
        // Ordena para que os livros NÃO devolvidos apareçam no topo da lista
        $emprestimos = Emprestimos::with([
            'livro',
            'membro.user',
            'pagamentoAprovado',
            'pagamentos',
            'eventos.user',
            'eventos.membro',
        ])
            ->orderByRaw("FIELD(status, 'solicitado','aprovado','retirado','em_uso','devolucao_solicitada','devolvido','encerrado','rejeitado','cancelado')")
            ->orderBy('data_devolucao_prevista', 'asc')
            ->get();

        $reservasAtivas = Reserva::with(['livro.autor', 'membro'])
            ->ativas()
            ->orderBy('livro_id')
            ->orderBy('created_at')
            ->get();

        return view('admin.emprestimos.index', compact('emprestimos', 'reservasAtivas'));
    }

    /**
     * Dá baixa no livro (Membro devolveu no balcão) e calcula multa (RN003)
     */
    public function devolver($id)
    {
        // 1. Acha o empréstimo no banco
        $emprestimo = Emprestimos::findOrFail($id);

        // Se a data_devolucao_real já estiver preenchida, o livro já foi devolvido
        if (in_array($emprestimo->status, [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO], true)) {
            return redirect()->back()->with('erro', 'Este livro já consta como devolvido no sistema!');
        }

        if (!in_array($emprestimo->status, [
            Emprestimos::STATUS_RETIRADO,
            Emprestimos::STATUS_EM_USO,
            Emprestimos::STATUS_DEVOLUCAO_SOLICITADA,
        ], true)) {
            return redirect()->back()->with('erro', 'Este empréstimo não está em uma etapa válida para devolução no balcão.');
        }

        $dataDevolucaoReal = $emprestimo->return_requested_at
            ? $emprestimo->return_requested_at->copy()->startOfDay()
            : Carbon::today();
        $valorMulta = Emprestimos::calcularMulta($emprestimo->data_devolucao_prevista, $dataDevolucaoReal);
        $diasAtraso = $valorMulta > 0
            ? (int) $emprestimo->data_devolucao_prevista->copy()->startOfDay()->diffInDays($dataDevolucaoReal)
            : 0;

        // 3. Atualiza o registro preenchendo a data real e a multa
        $emprestimo->update([
            'data_devolucao_real' => $dataDevolucaoReal,
            'valor_multa'         => $valorMulta,
            'status'              => Emprestimos::STATUS_DEVOLVIDO,
        ]);
        $emprestimo->registrarEvento(
            'emprestimo_devolvido',
            'Devolução recebida',
            $valorMulta > 0
                ? 'A devolução foi registrada com multa por atraso.'
                : ($emprestimo->return_requested_at ? 'A devolução foi registrada após solicitação do membro.' : 'A devolução foi registrada diretamente no balcão.'),
            [
                'data_devolucao_real' => $dataDevolucaoReal->format('d/m/Y'),
                'dias_atraso' => $diasAtraso,
                'multa' => $valorMulta,
            ]
        );

        // 4. Devolve o livro para a prateleira (Aumenta a quantidade do estoque em +1)
        $emprestimo->livro->increment('quantidade');
        AuditLog::record('emprestimo_devolvido', "Registrou devolução do livro {$emprestimo->livro?->titulo}.", $emprestimo, [
            'membro' => $emprestimo->membro?->nome,
            'multa' => $valorMulta,
        ]);

        // 5. Prepara a mensagem de sucesso (Avisa se teve multa)
        $mensagem = 'Livro devolvido com sucesso!';
        if ($valorMulta > 0) {
            $mensagem .= " Atenção: O membro atrasou $diasAtraso dia(s) e gerou uma multa de R$ " . number_format($valorMulta, 2, ',', '.') . ".";
        }

        return redirect()->back()->with('sucesso', $mensagem);
    }

    public function aprovar($id)
    {
        $emprestimo = Emprestimos::with(['livro', 'membro'])->findOrFail($id);

        if ($emprestimo->status !== Emprestimos::STATUS_SOLICITADO) {
            return redirect()->back()->with('erro', 'Somente solicitações podem ser aprovadas.');
        }

        if (!$emprestimo->membro) {
            return redirect()->back()->with('erro', 'Membro do empréstimo não encontrado.');
        }

        if ($motivo = Emprestimos::impedimentoParaNovoEmprestimo($emprestimo->membro_id, $emprestimo->livro_id, $emprestimo->id)) {
            return redirect()->back()->with('erro', $motivo);
        }

        if ($emprestimo->livro && $emprestimo->livro->quantidade <= 0) {
            return redirect()->back()->with('erro', 'Livro sem estoque para aprovar esta solicitação.');
        }

        $emprestimo->update([
            'status' => Emprestimos::STATUS_APROVADO,
            'approved_by' => auth()->guard('web')->id(),
            'approved_at' => Carbon::now(),
            'data_limite_retirada' => Emprestimos::prazoLimiteRetirada(),
        ]);
        $emprestimo->registrarEvento(
            'emprestimo_aprovado',
            'Solicitação aprovada',
            'A equipe aprovou a solicitação de empréstimo.',
            ['aprovado_em' => $emprestimo->approved_at?->format('d/m/Y H:i')]
        );

        if ($emprestimo->livro) {
            $emprestimo->livro->decrement('quantidade');
        }
        AuditLog::record('emprestimo_aprovado', "Aprovou solicitação de empréstimo do livro {$emprestimo->livro?->titulo}.", $emprestimo, [
            'membro' => $emprestimo->membro?->nome,
        ]);

        // Notifica o membro sobre aprovação
        if ($emprestimo->membro) {
            $emprestimo->membro->notify(new EmprestimoAprovado($emprestimo));
        }

        return redirect()->back()->with('sucesso', 'Solicitação aprovada. O exemplar ficou separado para retirada presencial.');
    }

    public function retirar(Request $request, $id)
    {
        $emprestimo = Emprestimos::with('livro')->findOrFail($id);

        if ($emprestimo->status !== Emprestimos::STATUS_APROVADO) {
            return redirect()->back()->with('erro', 'Somente empréstimos aprovados podem ser retirados.');
        }

        $hoje = Carbon::today();
        $prazoDias = Emprestimos::prazoDiasParaLivro($emprestimo->livro);

        $emprestimo->update([
            'status' => Emprestimos::STATUS_RETIRADO,
            'data_emprestimo' => $hoje,
            'data_devolucao_prevista' => $hoje->copy()->addDays($prazoDias),
            'data_limite_retirada' => null,
        ]);
        $emprestimo->registrarEvento(
            'emprestimo_retirado',
            'Retirada confirmada',
            'A biblioteca confirmou a retirada do livro.',
            [
                'retirada' => $hoje->format('d/m/Y'),
                'prazo' => $emprestimo->data_devolucao_prevista?->format('d/m/Y'),
                'prazo_dias' => $prazoDias,
            ]
        );

        // Notifica o membro que o empréstimo foi retirado
        if ($emprestimo->membro) {
            $emprestimo->membro->notify(new EmprestimoRetirado($emprestimo));
        }
        AuditLog::record('emprestimo_retirado', "Confirmou retirada do livro {$emprestimo->livro?->titulo}.", $emprestimo, [
            'membro' => $emprestimo->membro?->nome,
            'prazo' => $emprestimo->data_devolucao_prevista?->format('d/m/Y'),
        ]);

        return redirect()->back()->with('sucesso', "Retirada confirmada. Prazo de {$prazoDias} dias aplicado automaticamente.");
    }

    public function iniciarUso($id)
    {
        $emprestimo = Emprestimos::findOrFail($id);

        if ($emprestimo->status !== Emprestimos::STATUS_RETIRADO) {
            return redirect()->back()->with('erro', 'Somente empréstimos retirados podem entrar em uso.');
        }

        $emprestimo->update([
            'status' => Emprestimos::STATUS_EM_USO,
        ]);
        $emprestimo->registrarEvento(
            'emprestimo_em_uso',
            'Empréstimo em uso',
            'A equipe marcou o empréstimo como em uso.'
        );
        AuditLog::record('emprestimo_em_uso', 'Marcou empréstimo como em uso.', $emprestimo, [
            'membro' => $emprestimo->membro?->nome,
        ]);

        return redirect()->back()->with('sucesso', 'Empréstimo marcado como em uso.');
    }

    public function encerrar($id)
    {
        $emprestimo = Emprestimos::findOrFail($id);

        if ($emprestimo->status !== Emprestimos::STATUS_DEVOLVIDO) {
            return redirect()->back()->with('erro', 'Somente empréstimos devolvidos podem ser encerrados.');
        }

        $emprestimo->update([
            'status' => Emprestimos::STATUS_ENCERRADO,
        ]);
        $emprestimo->registrarEvento(
            'emprestimo_encerrado',
            'Empréstimo encerrado',
            'A equipe encerrou o ciclo do empréstimo.'
        );
        AuditLog::record('emprestimo_encerrado', 'Encerrou empréstimo devolvido.', $emprestimo, [
            'membro' => $emprestimo->membro?->nome,
        ]);

        return redirect()->back()->with('sucesso', 'Empréstimo encerrado.');
    }

    public function regularizarMulta($id)
    {
        $emprestimo = Emprestimos::with(['pagamentoAprovado', 'pagamentos'])->findOrFail($id);

        if (!$emprestimo->multaPendente()) {
            return redirect()->back()->with('erro', 'Este empréstimo não possui multa pendente.');
        }

        if ($emprestimo->pagamentos()->where('status', Pagamento::STATUS_PENDENTE)->exists()) {
            return redirect()->back()->with('erro', 'Esta multa possui pagamento em análise. Aprove ou recuse o pagamento antes de regularizar.');
        }

        if (!$emprestimo->pagamentoAprovado) {
            return redirect()->back()->with('erro', 'Esta multa ainda não possui pagamento aprovado. O membro precisa enviar o pagamento para conferência.');
        }

        $emprestimo->update([
            'multa_paga_em' => $emprestimo->pagamentoAprovado->pago_em ?? now(),
            'multa_regularizada_por' => auth()->guard('web')->id(),
        ]);
        $emprestimo->registrarEvento(
            'multa_regularizada',
            'Multa regularizada',
            'A equipe confirmou a regularização da multa após pagamento aprovado.',
            [
                'valor' => number_format((float) $emprestimo->valor_multa, 2, ',', '.'),
                'pagamento' => $emprestimo->pagamentoAprovado?->codigo,
            ]
        );
        AuditLog::record('multa_regularizada', 'Regularizou multa de empréstimo.', $emprestimo, [
            'membro' => $emprestimo->membro?->nome,
            'valor' => number_format((float) $emprestimo->valor_multa, 2, ',', '.'),
        ]);
        $this->notificarEquipeSobreMultaRegularizada($emprestimo);

        return redirect()->back()->with('sucesso', 'Multa regularizada com sucesso. O membro já pode solicitar novos empréstimos.');
    }

    private function notificarEquipeSobreMultaRegularizada(Emprestimos $emprestimo): void
    {
        $operador = auth()->guard('web')->user();
        $emprestimo->loadMissing(['membro', 'livro']);

        $equipe = User::whereIn('tipo_usuario', ['gerente', 'bibliotecario'])
            ->when($operador, fn ($query) => $query->whereKeyNot($operador->id))
            ->get();

        if ($equipe->isEmpty()) {
            return;
        }

        $nomeOperador = $operador?->name ?? 'Equipe';

        Notification::send($equipe, new EquipeOperacaoNotificada(
            'multa_regularizada_equipe',
            'Multa regularizada',
            "{$nomeOperador} regularizou a multa de {$emprestimo->membro?->nome} no livro '{$emprestimo->livro?->titulo}'.",
            [
                'emprestimo_id' => $emprestimo->id,
                'membro_id' => $emprestimo->membro_id,
                'operador_id' => $operador?->id,
                'operador_nome' => $operador?->name,
            ]
        ));
    }

    public function atenderReserva($id)
    {
        $reserva = Reserva::with(['livro', 'membro'])->findOrFail($id);

        if ($reserva->status !== Reserva::STATUS_ATIVA) {
            return redirect()->back()->with('erro', 'Esta reserva não está ativa.');
        }

        $primeiraDaFila = Reserva::ativas()
            ->where('livro_id', $reserva->livro_id)
            ->orderBy('created_at')
            ->first();

        if (!$primeiraDaFila || $primeiraDaFila->id !== $reserva->id) {
            return redirect()->back()->with('erro', 'Só é possível atender a primeira reserva da fila.');
        }

        if (!$reserva->livro || $reserva->livro->quantidade <= 0) {
            return redirect()->back()->with('erro', 'Ainda não há exemplar disponível para atender esta reserva.');
        }

        if (!$reserva->membro) {
            return redirect()->back()->with('erro', 'Membro da reserva não encontrado.');
        }

        if ($motivo = Emprestimos::impedimentoParaNovoEmprestimo($reserva->membro_id, $reserva->livro_id)) {
            return redirect()->back()->with('erro', $motivo);
        }

        $emprestimo = Emprestimos::create([
            'membro_id' => $reserva->membro_id,
            'livro_id' => $reserva->livro_id,
            'status' => Emprestimos::STATUS_APROVADO,
            'data_emprestimo' => null,
            'data_devolucao_prevista' => null,
            'data_limite_retirada' => Emprestimos::prazoLimiteRetirada(),
            'data_devolucao_real' => null,
            'valor_multa' => 0,
            'approved_by' => auth()->guard('web')->id(),
            'approved_at' => now(),
        ]);

        $reserva->livro->decrement('quantidade');
        $reserva->update(['status' => Reserva::STATUS_ATENDIDA]);

        $emprestimo->load('livro');
        $reserva->load('livro');
        $emprestimo->registrarEvento(
            'reserva_atendida',
            'Reserva atendida',
            'A equipe atendeu a primeira reserva da fila e gerou o empréstimo aprovado.',
            ['reserva_id' => $reserva->id]
        );
        $reserva->membro->notify(new ReservaDisponivel($reserva, $emprestimo));
        AuditLog::record('reserva_atendida', "Atendeu reserva do livro {$reserva->livro?->titulo}.", $reserva, [
            'membro' => $reserva->membro?->nome,
            'emprestimo_id' => $emprestimo->id,
        ]);

        return redirect()->back()->with('sucesso', 'Reserva atendida. O exemplar ficou aguardando retirada presencial.');
    }

    public function registrarBalcao(Request $request)
    {
        $dados = $request->validate([
            'membro_id' => ['required', 'exists:membros,id'],
            'livro_id' => ['required', 'exists:livros,id'],
        ]);

        $membroId = (int) $dados['membro_id'];
        $livroId = (int) $dados['livro_id'];

        if ($motivo = Emprestimos::impedimentoParaNovoEmprestimo($membroId, $livroId)) {
            return redirect()->back()->with('erro', $motivo);
        }

        $livro = \App\Models\Livros::with('autor')->findOrFail($livroId);
        $membro = \App\Models\Membros::findOrFail($membroId);

        if ((int) $livro->quantidade <= 0) {
            return redirect()->back()->with('erro', 'Não há exemplares disponíveis para empréstimo imediato.');
        }

        $hoje = Carbon::today();
        $prazoDias = Emprestimos::prazoDiasParaLivro($livro);

        $emprestimo = Emprestimos::create([
            'membro_id' => $membro->id,
            'livro_id' => $livro->id,
            'status' => Emprestimos::STATUS_RETIRADO,
            'data_emprestimo' => $hoje,
            'data_devolucao_prevista' => $hoje->copy()->addDays($prazoDias),
            'data_limite_retirada' => null,
            'data_devolucao_real' => null,
            'valor_multa' => 0,
            'approved_by' => auth()->guard('web')->id(),
            'approved_at' => now(),
        ]);

        $livro->decrement('quantidade');

        $emprestimo->registrarEvento(
            'emprestimo_balcao',
            'Empréstimo no balcão',
            'A equipe registrou um empréstimo presencial diretamente no atendimento.',
            [
                'retirada' => $hoje->format('d/m/Y'),
                'prazo' => $emprestimo->data_devolucao_prevista?->format('d/m/Y'),
                'prazo_dias' => $prazoDias,
            ]
        );

        AuditLog::record('emprestimo_balcao', "Registrou empréstimo presencial do livro {$livro->titulo}.", $emprestimo, [
            'membro' => $membro->nome,
            'prazo' => $emprestimo->data_devolucao_prevista?->format('d/m/Y'),
        ]);

        $membro->notify(new EmprestimoRetirado($emprestimo->load('livro')));

        return redirect()->back()->with('sucesso', "Empréstimo registrado no balcão. Prazo de devolução: {$emprestimo->data_devolucao_prevista?->format('d/m/Y')}.");
    }

    public function rejeitar(Request $request, $id)
    {
        $emprestimo = Emprestimos::findOrFail($id);

        if ($emprestimo->status !== Emprestimos::STATUS_SOLICITADO) {
            return redirect()->back()->with('erro', 'Somente solicitações podem ser rejeitadas.');
        }

        $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        $emprestimo->update([
            'status' => Emprestimos::STATUS_REJEITADO,
            'rejected_reason' => $request->input('motivo'),
            'rejected_at' => Carbon::now(),
            'rejected_by' => auth()->guard('web')->id(),
        ]);
        $emprestimo->registrarEvento(
            'emprestimo_rejeitado',
            'Solicitação rejeitada',
            'A equipe rejeitou a solicitação de empréstimo.',
            ['motivo' => $request->input('motivo')]
        );

        if ($emprestimo->membro) {
            $emprestimo->membro->notify(new EmprestimoRejeitado($emprestimo));
        }
        AuditLog::record('emprestimo_rejeitado', "Rejeitou solicitação de empréstimo do livro {$emprestimo->livro?->titulo}.", $emprestimo, [
            'membro' => $emprestimo->membro?->nome,
            'motivo' => $request->input('motivo'),
        ]);

        return redirect()->back()->with('sucesso', 'Solicitação rejeitada.');
    }
}
