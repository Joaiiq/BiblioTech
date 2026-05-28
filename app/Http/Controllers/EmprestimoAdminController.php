<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Emprestimos; // Usando o seu Model no plural!
use App\Models\Pagamento;
use App\Models\Reserva;
use App\Notifications\EmprestimoRejeitado;
use App\Notifications\EmprestimoAprovado;
use App\Notifications\EmprestimoRetirado;
use App\Notifications\ReservaDisponivel;
use App\Models\AuditLog;
use Carbon\Carbon;

class EmprestimoAdminController extends Controller
{
    /**
     * Lista todos os empréstimos para o bibliotecário administrar
     */
    public function index()
    {
        // Traz os empréstimos e já carrega os relacionamentos (livro, e o user dentro de membro)
        // Ordena para que os livros NÃO devolvidos apareçam no topo da lista
        $emprestimos = Emprestimos::with(['livro', 'membro.user', 'pagamentoAprovado', 'pagamentos'])
            ->orderByRaw("FIELD(status, 'solicitado','aprovado','retirado','em_uso','devolucao_solicitada','devolvido','encerrado','rejeitado')")
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

        if ($emprestimo->status !== Emprestimos::STATUS_DEVOLUCAO_SOLICITADA) {
            return redirect()->back()->with('erro', 'A devolução precisa ser solicitada pelo membro.');
        }

        $hoje = Carbon::today();
        $valorMulta = Emprestimos::calcularMulta($emprestimo->data_devolucao_prevista, $hoje);
        $diasAtraso = $valorMulta > 0
            ? (int) $emprestimo->data_devolucao_prevista->copy()->startOfDay()->diffInDays($hoje)
            : 0;

        // 3. Atualiza o registro preenchendo a data real e a multa
        $emprestimo->update([
            'data_devolucao_real' => $hoje,
            'valor_multa'         => $valorMulta,
            'status'              => Emprestimos::STATUS_DEVOLVIDO,
        ]);

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
        ]);

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

        return redirect()->back()->with('sucesso', 'Solicitação aprovada com sucesso.');
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
        ]);

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
        AuditLog::record('multa_regularizada', 'Regularizou multa de empréstimo.', $emprestimo, [
            'membro' => $emprestimo->membro?->nome,
            'valor' => number_format((float) $emprestimo->valor_multa, 2, ',', '.'),
        ]);

        return redirect()->back()->with('sucesso', 'Multa regularizada com sucesso. O membro já pode solicitar novos empréstimos.');
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
            'data_devolucao_real' => null,
            'valor_multa' => 0,
            'approved_by' => auth()->guard('web')->id(),
            'approved_at' => now(),
        ]);

        $reserva->livro->decrement('quantidade');
        $reserva->update(['status' => Reserva::STATUS_ATENDIDA]);

        $emprestimo->load('livro');
        $reserva->load('livro');
        $reserva->membro->notify(new ReservaDisponivel($reserva, $emprestimo));
        AuditLog::record('reserva_atendida', "Atendeu reserva do livro {$reserva->livro?->titulo}.", $reserva, [
            'membro' => $reserva->membro?->nome,
            'emprestimo_id' => $emprestimo->id,
        ]);

        return redirect()->back()->with('sucesso', 'Reserva atendida. O empréstimo foi aprovado e aguarda retirada.');
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
