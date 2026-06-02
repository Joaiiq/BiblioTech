<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Livros;
use App\Models\Emprestimos;
use App\Models\Membros; // Precisamos importar o model do Membro!
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\DevolucaoSolicitada;
use App\Notifications\EmprestimoSolicitado;
use App\Notifications\ReservaCancelada;
use App\Notifications\ReservaRegistrada;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class EmprestimoController extends Controller
{

    public function comprovante($id)
    {
        if ($response = $this->bloquearAcessoAdministrativo()) {
            return $response;
        }

        $membro = auth()->guard('membro')->user();

        // Garante que o membro só acessa o próprio comprovante
        $emprestimo = Emprestimos::with(['livro.autor', 'membro'])
            ->where('id', $id)
            ->where('membro_id', $membro->id)
            ->firstOrFail();

        if (!$emprestimo->data_emprestimo || !$emprestimo->data_devolucao_prevista) {
            return redirect()
                ->route('emprestimos.historico')
                ->with('erro', 'O comprovante fica disponível depois que a biblioteca confirmar a retirada.');
        }

        $pdf = Pdf::loadView('membros.comprovante-pdf', compact('emprestimo'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("comprovante-{$emprestimo->id}.pdf");
    }
    public function alugar($id)
    {
        if ($response = $this->bloquearAcessoAdministrativo()) {
            return $response;
        }

        // 1. Acha o livro no banco
        $livro = Livros::findOrFail($id);

        // 2. Verifica se o membro está autenticado (usando o guard 'membro')
        if (!auth()->guard('membro')->check()) {
            return redirect()->route('login')->with('erro', 'Você precisa estar logado como membro para alugar livros.');
        }

        // 3. Pega o membro autenticado pelo guard de membros.
        $membro = auth()->guard('membro')->user();

        if ($motivo = Emprestimos::impedimentoParaNovoEmprestimo($membro->id, $livro->id)) {
            return redirect()->back()->with('erro', $this->mensagemParaMembro($motivo));
        }

        // 4. Verifica o estoque
        if ($livro->quantidade <= 0) {
            return redirect()->back()->with('erro', 'Putz! Esse livro está fora de estoque no momento.');
        }

        $reservasAtivas = Reserva::ativas()
            ->where('livro_id', $livro->id)
            ->orderBy('created_at')
            ->get();

        if ($reservasAtivas->isNotEmpty()) {
            $primeiraReserva = $reservasAtivas->first();

            if ((int) $primeiraReserva->membro_id !== (int) $membro->id) {
                return redirect()->back()->with('erro', 'Este livro possui fila de reserva ativa. Aguarde a biblioteca atender a fila.');
            }

            return redirect()->back()->with('erro', 'Sua reserva é a primeira da fila. Aguarde a biblioteca liberar o empréstimo pelo atendimento.');
        }

        // 5. Cria o registro usando os nomes exatos da sua migration
        $emprestimo = Emprestimos::create([
            'membro_id' => $membro->id,
            'livro_id' => $livro->id,
            'status' => Emprestimos::STATUS_SOLICITADO,
            'data_emprestimo' => null,
            'data_devolucao_prevista' => null,
            'data_devolucao_real' => null,
            'valor_multa' => 0, // Começa sem multa
        ]);

        $emprestimo->load(['livro', 'membro']);
        $emprestimo->registrarEvento(
            'emprestimo_solicitado',
            'Solicitação criada',
            'O membro solicitou o empréstimo do livro.',
            ['livro' => $livro->titulo]
        );

        // Notifica os administradores (gerente e bibliotecario) sobre o novo pedido
        User::whereIn('tipo_usuario', ['gerente', 'bibliotecario'])
            ->get()
            ->each(function (User $admin) use ($emprestimo) {
                $admin->notify(new EmprestimoSolicitado($emprestimo));
            });

        return redirect()->back()->with('sucesso', 'Solicitação enviada! Aguarde a aprovação do bibliotecário.');
    }

    public function reservar($id)
    {
        if ($response = $this->bloquearAcessoAdministrativo()) {
            return $response;
        }

        $livro = Livros::findOrFail($id);

        if (!auth()->guard('membro')->check()) {
            return redirect()->route('login')->with('erro', 'Você precisa estar logado como membro para reservar livros.');
        }

        $membro = auth()->guard('membro')->user();

        if ($livro->quantidade > 0) {
            return redirect()->back()->with('erro', 'Este livro está disponível. Você já pode solicitar o empréstimo.');
        }

        $jaReservou = Reserva::ativas()
            ->where('membro_id', $membro->id)
            ->where('livro_id', $livro->id)
            ->exists();

        if ($jaReservou) {
            return redirect()->back()->with('erro', 'Você já está na fila de reserva deste livro.');
        }

        if ($motivo = Emprestimos::impedimentoParaNovoEmprestimo($membro->id, $livro->id)) {
            return redirect()->back()->with('erro', $this->mensagemParaMembro($motivo));
        }

        $reserva = Reserva::create([
            'membro_id' => $membro->id,
            'livro_id' => $livro->id,
            'status' => Reserva::STATUS_ATIVA,
        ]);

        $reserva->load(['livro', 'membro']);

        $posicao = Reserva::ativas()
            ->where('livro_id', $livro->id)
            ->where('created_at', '<=', now())
            ->count();

        User::whereIn('tipo_usuario', ['gerente', 'bibliotecario'])
            ->get()
            ->each(function (User $admin) use ($reserva) {
                $admin->notify(new ReservaRegistrada($reserva));
            });

        return redirect()->back()->with('sucesso', "Reserva registrada. Você entrou na posição {$posicao} da fila.");
    }

    public function cancelarReserva($id)
    {
        if ($response = $this->bloquearAcessoAdministrativo()) {
            return $response;
        }

        $membro = auth()->guard('membro')->user();

        $reserva = Reserva::ativas()
            ->where('id', $id)
            ->where('membro_id', $membro->id)
            ->firstOrFail();

        $reserva->load(['livro', 'membro']);

        $reserva->update([
            'status' => Reserva::STATUS_CANCELADA,
            'cancelada_em' => now(),
        ]);

        User::whereIn('tipo_usuario', ['gerente', 'bibliotecario'])
            ->get()
            ->each(function (User $admin) use ($reserva) {
                $admin->notify(new ReservaCancelada($reserva));
            });

        return redirect()->back()->with('sucesso', 'Reserva cancelada com sucesso.');
    }

    public function cancelarSolicitacao($id)
    {
        if ($response = $this->bloquearAcessoAdministrativo()) {
            return $response;
        }

        $membro = auth()->guard('membro')->user();

        $emprestimo = Emprestimos::with('livro')
            ->where('id', $id)
            ->where('membro_id', $membro->id)
            ->firstOrFail();

        if ($emprestimo->status !== Emprestimos::STATUS_SOLICITADO) {
            return redirect()->back()->with('erro', 'Só é possível cancelar solicitações que ainda aguardam aprovação.');
        }

        $emprestimo->update([
            'status' => Emprestimos::STATUS_CANCELADO,
            'rejected_reason' => 'Cancelado pelo membro antes da aprovação.',
            'rejected_at' => now(),
        ]);

        $emprestimo->registrarEvento(
            'emprestimo_cancelado',
            'Solicitação cancelada',
            'O membro cancelou o pedido antes da aprovação da biblioteca.',
            ['cancelado_em' => now()->format('d/m/Y H:i')]
        );

        return redirect()->back()->with('sucesso', 'Solicitação cancelada com sucesso.');
    }

    public function historico()
    {
        if ($response = $this->bloquearAcessoAdministrativo()) {
            return $response;
        }

        $membro = auth()->guard('membro')->user();

        $emprestimos = Emprestimos::with('livro.autor')
            ->where('membro_id', $membro->id)
            ->orderByRaw("FIELD(status, 'solicitado','aprovado','retirado','em_uso','devolucao_solicitada','devolvido','encerrado','rejeitado','cancelado')")
            ->orderBy('data_devolucao_prevista', 'asc')
            ->get();

        $reservas = Reserva::with('livro.autor')
            ->where('membro_id', $membro->id)
            ->latest()
            ->get()
            ->map(function (Reserva $reserva) {
                if ($reserva->status === Reserva::STATUS_ATIVA) {
                    $reserva->posicao_fila = Reserva::ativas()
                        ->where('livro_id', $reserva->livro_id)
                        ->where('created_at', '<=', $reserva->created_at)
                        ->count();
                }

                return $reserva;
            });

        return view('membros.historico', compact('emprestimos', 'reservas'));
    }

    public function solicitarDevolucao($id)
    {
        if ($response = $this->bloquearAcessoAdministrativo()) {
            return $response;
        }

        $membro = auth()->guard('membro')->user();

        $emprestimo = Emprestimos::where('id', $id)
            ->where('membro_id', $membro->id)
            ->firstOrFail();

        if (!in_array($emprestimo->status, [Emprestimos::STATUS_RETIRADO, Emprestimos::STATUS_EM_USO], true)) {
            return redirect()->back()->with('erro', 'Este empréstimo não pode solicitar devolução agora.');
        }

        $emprestimo->update([
            'status' => Emprestimos::STATUS_DEVOLUCAO_SOLICITADA,
            'return_requested_at' => Carbon::now(),
        ]);
        $emprestimo->registrarEvento(
            'devolucao_solicitada',
            'Devolução solicitada',
            'O membro informou que deseja devolver o livro.',
            ['solicitada_em' => $emprestimo->return_requested_at?->format('d/m/Y H:i')]
        );

        // Notifica os administradores (gerente e bibliotecario) sobre a solicitação
        User::whereIn('tipo_usuario', ['gerente', 'bibliotecario'])
            ->get()
            ->each(function (User $admin) use ($emprestimo) {
                $admin->notify(new DevolucaoSolicitada($emprestimo));
            });

        return redirect()->back()->with('sucesso', 'Solicitação de devolução enviada.');
    }

    public function renovar($id)
    {
        if ($response = $this->bloquearAcessoAdministrativo()) {
            return $response;
        }

        $membro = auth()->guard('membro')->user();

        $emprestimo = Emprestimos::with('livro')
            ->where('id', $id)
            ->where('membro_id', $membro->id)
            ->firstOrFail();

        if (!$emprestimo->podeRenovar()) {
            return redirect()->back()->with('erro', 'Este empréstimo não pode ser renovado agora.');
        }

        if (Emprestimos::possuiMultaPendente($membro->id)) {
            return redirect()->back()->with('erro', 'Você possui multas pendentes. Regularize sua situação para renovar empréstimos.');
        }

        $temReservaNaFila = Reserva::ativas()
            ->where('livro_id', $emprestimo->livro_id)
            ->exists();

        if ($temReservaNaFila) {
            return redirect()->back()->with('erro', 'Este livro possui reservas em fila, então não pode ser renovado.');
        }

        $prazoDias = Emprestimos::prazoDiasParaLivro($emprestimo->livro);
        $base = $emprestimo->data_devolucao_prevista->isFuture()
            ? $emprestimo->data_devolucao_prevista->copy()
            : Carbon::today();

        $emprestimo->update([
            'data_devolucao_prevista' => $base->addDays($prazoDias),
            'renovacoes_count' => ((int) $emprestimo->renovacoes_count) + 1,
            'ultima_renovacao_em' => now(),
        ]);
        $emprestimo->registrarEvento(
            'emprestimo_renovado',
            'Prazo renovado',
            "O membro renovou o empréstimo por mais {$prazoDias} dias.",
            [
                'novo_prazo' => $emprestimo->data_devolucao_prevista?->format('d/m/Y'),
                'renovacoes' => $emprestimo->renovacoes_count,
            ]
        );

        return redirect()->back()->with('sucesso', "Empréstimo renovado por mais {$prazoDias} dias.");
    }

    private function bloquearAcessoAdministrativo()
    {
        if (Auth::guard('web')->check()) {
            return redirect()
                ->route('dashboard')
                ->with('erro', 'Contas administrativas não podem solicitar, reservar ou gerenciar empréstimos como membro.');
        }

        return null;
    }

    private function mensagemParaMembro(string $motivo): string
    {
        return str_replace('O membro', 'Você', $motivo);
    }
}
