<?php

namespace App\Http\Controllers;

use App\Models\Emprestimos;
use App\Models\Livros;
use App\Models\Membros;
use App\Models\Reserva;

class OperacaoController extends Controller
{
    public function index()
    {
        Emprestimos::expirarRetiradasPendentes();

        $solicitacoes = Emprestimos::with(['livro.autor', 'membro'])
            ->where('status', Emprestimos::STATUS_SOLICITADO)
            ->latest()
            ->take(8)
            ->get();

        $aprovados = Emprestimos::with(['livro.autor', 'membro'])
            ->where('status', Emprestimos::STATUS_APROVADO)
            ->latest('approved_at')
            ->take(8)
            ->get();

        $devolucoesSolicitadas = Emprestimos::with(['livro.autor', 'membro'])
            ->where('status', Emprestimos::STATUS_DEVOLUCAO_SOLICITADA)
            ->orderBy('return_requested_at')
            ->take(8)
            ->get();

        $atrasados = Emprestimos::with(['livro.autor', 'membro'])
            ->whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
            ->whereDate('data_devolucao_prevista', '<', today())
            ->orderBy('data_devolucao_prevista')
            ->take(8)
            ->get();

        $vencendoHoje = Emprestimos::with(['livro.autor', 'membro'])
            ->whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
            ->whereDate('data_devolucao_prevista', today())
            ->orderBy('data_devolucao_prevista')
            ->take(8)
            ->get();

        $multasPendentes = Emprestimos::with(['livro.autor', 'membro', 'pagamentoAprovado', 'pagamentos'])
            ->where('valor_multa', '>', 0)
            ->whereNull('multa_paga_em')
            ->orderByDesc('valor_multa')
            ->take(8)
            ->get();

        $reservasAtendiveis = Reserva::with(['livro.autor', 'membro'])
            ->ativas()
            ->whereHas('livro', fn ($query) => $query->where('quantidade', '>', 0))
            ->orderBy('livro_id')
            ->orderBy('created_at')
            ->get()
            ->groupBy('livro_id')
            ->map(fn ($reservas) => $reservas->first())
            ->values()
            ->take(8);

        $metricas = [
            'solicitacoes' => Emprestimos::where('status', Emprestimos::STATUS_SOLICITADO)->count(),
            'aprovados' => Emprestimos::where('status', Emprestimos::STATUS_APROVADO)->count(),
            'devolucoes' => Emprestimos::where('status', Emprestimos::STATUS_DEVOLUCAO_SOLICITADA)->count(),
            'atrasados' => Emprestimos::whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
                ->whereDate('data_devolucao_prevista', '<', today())
                ->count(),
            'vencendo_hoje' => Emprestimos::whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
                ->whereDate('data_devolucao_prevista', today())
                ->count(),
            'multas_pendentes' => Emprestimos::where('valor_multa', '>', 0)
                ->whereNull('multa_paga_em')
                ->count(),
            'total_multas' => (float) Emprestimos::where('valor_multa', '>', 0)
                ->whereNull('multa_paga_em')
                ->sum('valor_multa'),
            'reservas_atendiveis' => $reservasAtendiveis->count(),
        ];

        $membrosBalcao = Membros::query()
            ->orderBy('nome')
            ->get(['id', 'nome', 'cpf', 'numero_carteirinha']);

        $livrosBalcao = Livros::with('autor')
            ->where('quantidade', '>', 0)
            ->orderBy('titulo')
            ->get(['id', 'titulo', 'autor_id', 'quantidade', 'e_bestseller']);

        return view('admin.operacao.index', compact(
            'solicitacoes',
            'aprovados',
            'devolucoesSolicitadas',
            'atrasados',
            'vencendoHoje',
            'multasPendentes',
            'reservasAtendiveis',
            'metricas',
            'membrosBalcao',
            'livrosBalcao',
        ));
    }
}
