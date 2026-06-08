<?php

namespace App\Http\Controllers;

use App\Models\Emprestimos;
use App\Models\Livros;
use App\Models\Membros;
use App\Models\Reserva;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Rules\RealisticDate;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.relatorios.index', $this->dadosRelatorio($request));
    }

    public function exportarPdf(Request $request)
    {
        $dados = $this->dadosRelatorio($request);
        $nomeArquivo = 'relatorio-bibliotech-' . $dados['tipoRelatorio'] . '-' . $dados['inicio']->format('Y-m-d') . '-' . $dados['fim']->format('Y-m-d') . '.pdf';

        return Pdf::loadView('admin.relatorios.pdf', $dados)
            ->setPaper('a4', 'landscape')
            ->download($nomeArquivo);
    }

    private function dadosRelatorio(Request $request): array
    {
        $request->validate([
            'inicio' => ['nullable', 'date_format:Y-m-d', new RealisticDate('period')],
            'fim' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:inicio', new RealisticDate('period')],
        ], [
            'fim.after_or_equal' => 'A data final precisa ser igual ou posterior à data inicial.',
        ]);

        $inicio = $request->filled('inicio')
            ? Carbon::parse($request->input('inicio'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $fim = $request->filled('fim')
            ? Carbon::parse($request->input('fim'))->endOfDay()
            : now()->endOfDay();

        if ($inicio->greaterThan($fim)) {
            [$inicio, $fim] = [$fim->copy()->startOfDay(), $inicio->copy()->endOfDay()];
        }

        $tiposPermitidos = ['visao_geral', 'acervo', 'circulacao', 'membros', 'financeiro', 'operacao'];
        $tipoRelatorio = in_array($request->input('tipo'), $tiposPermitidos, true)
            ? $request->input('tipo')
            : 'visao_geral';

        $emprestimosPeriodo = Emprestimos::with(['livro.autor', 'membro', 'aprovadoPor'])
            ->whereBetween('data_emprestimo', [$inicio->toDateString(), $fim->toDateString()])
            ->latest('data_emprestimo')
            ->get();

        $emprestimosAtivos = Emprestimos::with(['livro', 'membro'])
            ->whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
            ->orderBy('data_devolucao_prevista')
            ->get();

        $atrasados = $emprestimosAtivos
            ->filter(fn (Emprestimos $emprestimo) => $emprestimo->isAtrasado())
            ->values();

        $livros = Livros::with('autor')->orderBy('titulo')->get();
        $emprestimosAtivosPorLivro = Emprestimos::whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
            ->selectRaw('livro_id, COUNT(*) as total')
            ->groupBy('livro_id')
            ->pluck('total', 'livro_id');

        $acervo = $livros
            ->groupBy(fn (Livros $livro) => mb_strtolower($livro->titulo) . '|' . ($livro->autor_id ?? 'sem-autor') . '|' . mb_strtolower($livro->categoria ?? 'sem-categoria') . '|' . mb_strtolower($livro->estante ?? 'sem-estante') . '|' . mb_strtolower($livro->localizacao ?? 'sem-localizacao'))
            ->map(function ($grupo) use ($emprestimosAtivosPorLivro) {
                $livro = clone $grupo->first();
                $quantidade = $grupo->sum('quantidade');
                $emprestados = $grupo->sum(fn (Livros $item) => (int) ($emprestimosAtivosPorLivro[$item->id] ?? 0));
                $livro->quantidade = $quantidade;

                return [
                    'livro' => $livro,
                    'livro_ids' => $grupo->pluck('id')->all(),
                    'emprestados' => $emprestados,
                    'disponiveis' => max(0, (int) $quantidade - $emprestados),
                ];
            })
            ->sortBy(fn ($item) => $item['livro']->titulo)
            ->values();

        $livrosMaisLidos = $emprestimosPeriodo
            ->filter(fn (Emprestimos $emprestimo) => $emprestimo->livro)
            ->groupBy(fn (Emprestimos $emprestimo) => mb_strtolower($emprestimo->livro->titulo) . '|' . ($emprestimo->livro->autor_id ?? 'sem-autor'))
            ->map(fn ($grupo) => [
                'livro' => $grupo->first()->livro,
                'total' => $grupo->count(),
            ])
            ->sortByDesc('total')
            ->take(8)
            ->values();

        $reservasAtivas = Schema::hasTable('reservas')
            ? Reserva::with(['livro.autor', 'membro'])->ativas()->oldest()->get()
            : collect();

        $reservasAtivasPorLivro = $reservasAtivas
            ->groupBy('livro_id')
            ->map(fn ($grupo) => $grupo->count());

        $reservasFila = $reservasAtivas
            ->filter(fn (Reserva $reserva) => $reserva->livro)
            ->groupBy('livro_id')
            ->map(function ($grupo) {
                $primeiraReserva = $grupo->sortBy('created_at')->first();
                $esperaMedia = (int) round($grupo->avg(fn (Reserva $reserva) => $reserva->created_at?->diffInDays(now()) ?? 0));

                return [
                    'livro' => $primeiraReserva->livro,
                    'fila' => $grupo->count(),
                    'primeira_reserva' => $primeiraReserva->created_at,
                    'espera_media' => $esperaMedia,
                    'membros' => $grupo->pluck('membro.nome')->filter()->take(3)->values(),
                ];
            })
            ->sortByDesc('fila')
            ->values();

        $circulacaoPorLivro = $emprestimosPeriodo
            ->filter(fn (Emprestimos $emprestimo) => $emprestimo->livro_id)
            ->groupBy('livro_id')
            ->map(fn ($grupo) => $grupo->count());

        $sugestoesCompra = $acervo
            ->map(function ($item) use ($reservasAtivasPorLivro, $circulacaoPorLivro) {
                $livro = $item['livro'];
                $livroIds = collect($item['livro_ids'] ?? [$livro->id]);
                $reservas = (int) $livroIds->sum(fn ($livroId) => $reservasAtivasPorLivro[$livroId] ?? 0);
                $circulacao = (int) $livroIds->sum(fn ($livroId) => $circulacaoPorLivro[$livroId] ?? 0);
                $disponiveis = (int) $item['disponiveis'];
                $estoque = (int) $livro->quantidade;

                if ($reservas === 0 && $circulacao === 0 && $disponiveis > 1) {
                    return null;
                }

                $motivos = [];

                if ($reservas > 0) {
                    $motivos[] = $reservas === 1 ? '1 reserva aguardando' : "{$reservas} reservas aguardando";
                }

                if ($disponiveis === 0) {
                    $motivos[] = 'sem exemplar disponível';
                } elseif ($disponiveis === 1) {
                    $motivos[] = 'apenas 1 exemplar disponível';
                }

                if ($circulacao > 0) {
                    $motivos[] = $circulacao === 1 ? '1 empréstimo no período' : "{$circulacao} empréstimos no período";
                }

                $prioridade = match (true) {
                    $reservas >= 2 || ($disponiveis === 0 && $circulacao >= 2) => 'Alta',
                    $reservas === 1 || $disponiveis <= 1 || $circulacao >= 2 => 'Média',
                    default => 'Baixa',
                };

                return [
                    'livro' => $livro,
                    'estoque' => $estoque,
                    'disponiveis' => $disponiveis,
                    'reservas' => $reservas,
                    'circulacao' => $circulacao,
                    'prioridade' => $prioridade,
                    'motivo' => implode(', ', $motivos),
                    'quantidade_sugerida' => max(1, $reservas - $disponiveis, (int) ceil($circulacao / 3)),
                ];
            })
            ->filter()
            ->sortByDesc(function ($item) {
                $pesoPrioridade = [
                    'Alta' => 3,
                    'Média' => 2,
                    'Baixa' => 1,
                ][$item['prioridade']] ?? 0;

                return ($pesoPrioridade * 1000) + ($item['reservas'] * 100) + ($item['circulacao'] * 10) + $item['quantidade_sugerida'];
            })
            ->take(10)
            ->values();

        $categorias = $emprestimosPeriodo
            ->filter(fn (Emprestimos $emprestimo) => filled($emprestimo->livro?->categoria))
            ->groupBy(fn (Emprestimos $emprestimo) => $emprestimo->livro->categoria)
            ->map(fn ($grupo, $categoria) => [
                'categoria' => $categoria,
                'total' => $grupo->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $perfisLeitores = $emprestimosPeriodo
            ->filter(fn (Emprestimos $emprestimo) => $emprestimo->membro)
            ->groupBy(function (Emprestimos $emprestimo) {
                $membro = $emprestimo->membro;

                return $this->faixaEtaria($membro->data_nascimento) . '|membro';
            })
            ->map(function ($grupo, $chave) {
                [$faixaEtaria] = explode('|', $chave);

                $categoriasPreferidas = $grupo
                    ->filter(fn (Emprestimos $emprestimo) => filled($emprestimo->livro?->categoria))
                    ->groupBy(fn (Emprestimos $emprestimo) => $emprestimo->livro->categoria)
                    ->map(fn ($emprestimos, $categoria) => [
                        'categoria' => $categoria,
                        'total' => $emprestimos->count(),
                    ])
                    ->sortByDesc('total')
                    ->values();

                return [
                    'faixa_etaria' => $faixaEtaria,
                    'tipo_membro' => 'Membro',
                    'leitores' => $grupo->pluck('membro_id')->unique()->count(),
                    'emprestimos' => $grupo->count(),
                    'categoria_preferida' => $categoriasPreferidas->first()['categoria'] ?? 'Sem categoria',
                    'categoria_total' => $categoriasPreferidas->first()['total'] ?? 0,
                ];
            })
            ->sortByDesc('emprestimos')
            ->values();

        $sazonalidade = $emprestimosPeriodo
            ->filter(fn (Emprestimos $emprestimo) => $emprestimo->data_emprestimo)
            ->groupBy(fn (Emprestimos $emprestimo) => $emprestimo->data_emprestimo->format('Y-m') . '|' . ($emprestimo->livro?->categoria ?: 'Sem categoria'))
            ->map(function ($grupo, $chave) {
                [$anoMes, $categoria] = explode('|', $chave);
                $mes = Carbon::createFromFormat('Y-m', $anoMes)->locale('pt_BR');

                return [
                    'mes' => ucfirst($mes->translatedFormat('M/Y')),
                    'categoria' => $categoria,
                    'total' => $grupo->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        $sazonalidadeMensal = $emprestimosPeriodo
            ->filter(fn (Emprestimos $emprestimo) => $emprestimo->data_emprestimo)
            ->groupBy(fn (Emprestimos $emprestimo) => $emprestimo->data_emprestimo->format('Y-m'))
            ->map(function ($grupo, $anoMes) {
                $mes = Carbon::createFromFormat('Y-m', $anoMes)->locale('pt_BR');

                return [
                    'mes' => ucfirst($mes->translatedFormat('M/Y')),
                    'total' => $grupo->count(),
                ];
            })
            ->sortBy('mes')
            ->values();

        $desempenhoBibliotecarios = $emprestimosPeriodo
            ->filter(fn (Emprestimos $emprestimo) => $emprestimo->aprovadoPor)
            ->groupBy('approved_by')
            ->map(fn ($grupo) => [
                'bibliotecario' => $grupo->first()->aprovadoPor,
                'atendimentos' => $grupo->count(),
                'devolucoes' => $grupo->whereIn('status', [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO])->count(),
            ])
            ->sortByDesc('atendimentos')
            ->values();

        $multasPendentes = Emprestimos::with(['livro', 'membro'])
            ->where('valor_multa', '>', 0)
            ->whereNull('multa_paga_em')
            ->whereIn('status', [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO])
            ->latest('data_devolucao_real')
            ->get();

        $metricas = [
            'livros' => $acervo->count(),
            'exemplares' => $livros->sum('quantidade'),
            'membros' => Membros::count(),
            'bibliotecarios' => User::whereIn('tipo_usuario', ['gerente', 'bibliotecario'])->count(),
            'emprestimosPeriodo' => $emprestimosPeriodo->count(),
            'ativos' => $emprestimosAtivos->count(),
            'atrasados' => $atrasados->count(),
            'multas' => $multasPendentes->sum('valor_multa'),
        ];

        $emprestimosPorDia = $emprestimosPeriodo
            ->groupBy(fn (Emprestimos $emprestimo) => optional($emprestimo->data_emprestimo)->format('d/m'))
            ->map(fn ($grupo, $data) => [
                'data' => $data,
                'total' => $grupo->count(),
            ])
            ->values();

        $statusDistribuicao = collect([
            'Solicitados' => $emprestimosPeriodo->where('status', Emprestimos::STATUS_SOLICITADO)->count(),
            'Aprovados' => $emprestimosPeriodo->where('status', Emprestimos::STATUS_APROVADO)->count(),
            'Em uso' => $emprestimosPeriodo->whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)->count(),
            'Concluídos' => $emprestimosPeriodo->whereIn('status', [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO])->count(),
            'Rejeitados' => $emprestimosPeriodo->where('status', Emprestimos::STATUS_REJEITADO)->count(),
            'Cancelados' => $emprestimosPeriodo->where('status', Emprestimos::STATUS_CANCELADO)->count(),
        ])->map(fn ($total, $status) => compact('status', 'total'))->values();

        $graficos = [
            'livrosLabels' => $livrosMaisLidos->pluck('livro.titulo')->values(),
            'livrosValores' => $livrosMaisLidos->pluck('total')->values(),
            'categoriasLabels' => $categorias->pluck('categoria')->values(),
            'categoriasValores' => $categorias->pluck('total')->values(),
            'diasLabels' => $emprestimosPorDia->pluck('data')->values(),
            'diasValores' => $emprestimosPorDia->pluck('total')->values(),
            'statusLabels' => $statusDistribuicao->pluck('status')->values(),
            'statusValores' => $statusDistribuicao->pluck('total')->values(),
            'sazonalidadeLabels' => $sazonalidadeMensal->pluck('mes')->values(),
            'sazonalidadeValores' => $sazonalidadeMensal->pluck('total')->values(),
        ];

        return compact(
            'inicio',
            'fim',
            'tipoRelatorio',
            'metricas',
            'acervo',
            'emprestimosAtivos',
            'atrasados',
            'livrosMaisLidos',
            'sugestoesCompra',
            'categorias',
            'perfisLeitores',
            'sazonalidade',
            'reservasFila',
            'desempenhoBibliotecarios',
            'multasPendentes',
            'graficos'
        );
    }

    private function faixaEtaria($dataNascimento): string
    {
        if (blank($dataNascimento)) {
            return 'Não informada';
        }

        $idade = Carbon::parse($dataNascimento)->age;

        return match (true) {
            $idade <= 12 => 'Até 12 anos',
            $idade <= 17 => '13 a 17 anos',
            $idade <= 25 => '18 a 25 anos',
            $idade <= 40 => '26 a 40 anos',
            $idade <= 60 => '41 a 60 anos',
            default => 'Acima de 60 anos',
        };
    }
}
