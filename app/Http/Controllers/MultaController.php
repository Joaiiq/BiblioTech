<?php

namespace App\Http\Controllers;

use App\Models\Emprestimos;
use App\Models\Membros;
use App\Rules\RealisticDate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MultaController extends Controller
{
    public function index(Request $request)
    {
        $dados = $this->dadosMultas($request, true);

        return view('admin.multas.index', $dados);
    }

    public function exportarPdf(Request $request)
    {
        $dados = $this->dadosMultas($request, false);
        $nomeArquivo = 'multas-bibliotech-' . now()->format('Y-m-d-His') . '.pdf';

        return Pdf::loadView('admin.multas.pdf', $dados)
            ->setPaper('a4', 'landscape')
            ->download($nomeArquivo);
    }

    public function exportarCsv(Request $request)
    {
        $dados = $this->dadosMultas($request, false);
        $nomeArquivo = 'multas-bibliotech-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($dados) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Membro', 'E-mail', 'Carteirinha', 'Livro', 'Autor', 'Prazo', 'Devolução', 'Valor', 'Situação', 'Regularizada em']);

            foreach ($dados['multas'] as $multa) {
                fputcsv($handle, [
                    $multa->membro?->nome,
                    $multa->membro?->email,
                    $multa->membro?->numero_carteirinha,
                    $multa->livro?->titulo,
                    $multa->livro?->autor?->nome,
                    $multa->data_devolucao_prevista?->format('d/m/Y'),
                    $multa->data_devolucao_real?->format('d/m/Y'),
                    number_format((float) $multa->valor_multa, 2, ',', '.'),
                    $multa->multaPendente() ? 'Pendente' : 'Regularizada',
                    $multa->multa_paga_em?->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $nomeArquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function dadosMultas(Request $request, bool $paginated): array
    {
        $request->validate([
            'inicio' => ['nullable', 'date_format:Y-m-d', new RealisticDate('period')],
            'fim' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:inicio', new RealisticDate('period')],
        ], [
            'fim.after_or_equal' => 'A data final precisa ser igual ou posterior à data inicial.',
        ]);

        $status = $request->input('status', 'pendentes');
        $membroBusca = trim((string) $request->input('membro', ''));
        $valorMinimo = $request->filled('valor_minimo') ? (float) str_replace(',', '.', $request->input('valor_minimo')) : null;
        $inicio = $request->date('inicio');
        $fim = $request->date('fim');
        $baseQuery = Emprestimos::with([
            'membro',
            'livro.autor',
            'regularizadaPor',
            'pagamentoAprovado',
            'pagamentos' => fn ($query) => $query->latest(),
        ])
            ->where('valor_multa', '>', 0);

        $multasQuery = (clone $baseQuery)
            ->when($status === 'pendentes', fn ($query) => $query->whereNull('multa_paga_em'))
            ->when($status === 'regularizadas', fn ($query) => $query->whereNotNull('multa_paga_em'))
            ->when($membroBusca !== '', function ($query) use ($membroBusca) {
                $query->whereHas('membro', function ($membroQuery) use ($membroBusca) {
                    $membroQuery->where('nome', 'like', "%{$membroBusca}%")
                        ->orWhere('email', 'like', "%{$membroBusca}%")
                        ->orWhere('cpf', 'like', "%{$membroBusca}%")
                        ->orWhere('numero_carteirinha', 'like', "%{$membroBusca}%");
                });
            })
            ->when($valorMinimo !== null, fn ($query) => $query->where('valor_multa', '>=', $valorMinimo))
            ->when($inicio, fn ($query) => $query->whereDate('data_devolucao_real', '>=', $inicio))
            ->when($fim, fn ($query) => $query->whereDate('data_devolucao_real', '<=', $fim))
            ->orderByRaw('multa_paga_em IS NOT NULL')
            ->orderByDesc('valor_multa')
            ->orderByDesc('data_devolucao_real');

        $multas = $paginated
            ? $multasQuery->paginate(12)->withQueryString()
            : $multasQuery->get();

        $pendentes = (clone $baseQuery)->whereNull('multa_paga_em');
        $regularizadas = (clone $baseQuery)->whereNotNull('multa_paga_em');

        $metricas = [
            'total_pendente' => (clone $pendentes)->sum('valor_multa'),
            'total_arrecadado' => (clone $regularizadas)->sum('valor_multa'),
            'membros_inadimplentes' => (clone $pendentes)->distinct('membro_id')->count('membro_id'),
            'multas_pendentes' => (clone $pendentes)->count(),
            'multas_regularizadas' => (clone $regularizadas)->count(),
            'maior_multa' => (clone $baseQuery)->max('valor_multa') ?? 0,
        ];

        $maioresDevedores = Emprestimos::query()
            ->selectRaw('membro_id, SUM(valor_multa) as total, COUNT(*) as multas')
            ->with('membro')
            ->where('valor_multa', '>', 0)
            ->whereNull('multa_paga_em')
            ->groupBy('membro_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $membros = Membros::orderBy('nome')->get(['id', 'nome', 'email', 'numero_carteirinha']);

        return compact(
            'multas',
            'metricas',
            'maioresDevedores',
            'membros',
            'status',
            'membroBusca',
            'valorMinimo',
            'inicio',
            'fim'
        );
    }
}
