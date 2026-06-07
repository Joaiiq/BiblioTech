<?php

namespace App\Http\Controllers;

use App\Models\Emprestimos;
use App\Models\Livros;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MinhaBibliotecaController extends Controller
{
    public function index()
    {
        Emprestimos::expirarRetiradasPendentes();

        $membro = auth()->guard('membro')->user();

        $emprestimos = Emprestimos::with(['livro.autor', 'pagamentos'])
            ->where('membro_id', $membro->id)
            ->latest()
            ->get();

        $ativos = $emprestimos
            ->whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
            ->sortBy('data_devolucao_prevista')
            ->values();

        $ativosComProgresso = $ativos->map(function (Emprestimos $emprestimo) {
            $inicio = $emprestimo->data_emprestimo?->copy()->startOfDay();
            $fim = $emprestimo->data_devolucao_prevista?->copy()->startOfDay();
            $hoje = Carbon::today();
            $atrasado = $emprestimo->isAtrasado();
            $diasRestantes = $fim ? $hoje->diffInDays($fim, false) : null;
            $totalDias = ($inicio && $fim) ? max(1, $inicio->diffInDays($fim)) : 1;
            $diasPassados = $inicio ? max(0, $inicio->diffInDays($hoje, false)) : 0;
            $progresso = $fim ? min(100, max(0, (int) round(($diasPassados / $totalDias) * 100))) : 0;
            $risco = $atrasado ? 'atrasado' : ($progresso >= 80 ? 'risco' : 'ok');

            return [
                'emprestimo' => $emprestimo,
                'dias_restantes' => $diasRestantes,
                'total_dias' => $totalDias,
                'progresso' => $progresso,
                'risco' => $risco,
            ];
        });

        $historicoRecente = $emprestimos
            ->whereIn('status', [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO])
            ->take(5)
            ->values();

        $concluidos = $emprestimos
            ->whereIn('status', [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO])
            ->values();

        $atrasados = $emprestimos->filter(fn (Emprestimos $emprestimo) => $emprestimo->isAtrasado());
        $multasPendentes = $emprestimos->filter(fn (Emprestimos $emprestimo) => $emprestimo->multaPendente());

        $reservas = Schema::hasTable('reservas')
            ? Reserva::with('livro.autor')
                ->where('membro_id', $membro->id)
                ->latest()
                ->take(5)
                ->get()
                ->map(function (Reserva $reserva) {
                    if ($reserva->status === Reserva::STATUS_ATIVA) {
                        $reserva->posicao_fila = Reserva::ativas()
                            ->where('livro_id', $reserva->livro_id)
                            ->where('created_at', '<=', $reserva->created_at)
                            ->count();
                    }

                    return $reserva;
                })
            : collect();

        $favoritos = Schema::hasTable('favoritos')
            ? $membro->livrosFavoritos()
                ->with('autor')
                ->orderByPivot('created_at', 'desc')
                ->take(6)
                ->get()
            : collect();

        $categoriasPreferidas = Emprestimos::where('membro_id', $membro->id)
            ->join('livros', 'emprestimos.livro_id', '=', 'livros.id')
            ->whereNotNull('livros.categoria')
            ->whereIn('emprestimos.status', [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO])
            ->select('livros.categoria', DB::raw('COUNT(*) as total'))
            ->groupBy('livros.categoria')
            ->orderByDesc('total')
            ->take(4)
            ->get();

        $autoresPreferidos = Emprestimos::where('membro_id', $membro->id)
            ->join('livros', 'emprestimos.livro_id', '=', 'livros.id')
            ->join('autores', 'livros.autor_id', '=', 'autores.id')
            ->whereIn('emprestimos.status', [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO])
            ->select('autores.nome', DB::raw('COUNT(*) as total'))
            ->groupBy('autores.id', 'autores.nome')
            ->orderByDesc('total')
            ->take(3)
            ->get();

        $leiturasNoMes = $concluidos
            ->filter(fn (Emprestimos $emprestimo) => $emprestimo->data_devolucao_real?->isSameMonth(Carbon::now()))
            ->count();

        $categoriasLidas = $categoriasPreferidas->count();
        $totalConcluidos = $concluidos->count();
        $semPendencias = $atrasados->isEmpty() && $multasPendentes->isEmpty();
        $badges = collect([
            [
                'titulo' => 'Primeira devolução',
                'descricao' => 'Concluiu pelo menos uma leitura.',
                'icone' => 'ph-seal-check',
                'ativo' => $totalConcluidos >= 1,
                'cor' => 'emerald',
            ],
            [
                'titulo' => 'Leitor assíduo',
                'descricao' => 'Concluiu 5 ou mais empréstimos.',
                'icone' => 'ph-books',
                'ativo' => $totalConcluidos >= 5,
                'cor' => 'blue',
            ],
            [
                'titulo' => 'Sem pendências',
                'descricao' => 'Sem atrasos ou multas abertas.',
                'icone' => 'ph-shield-check',
                'ativo' => $semPendencias,
                'cor' => 'emerald',
            ],
            [
                'titulo' => 'Explorador de gêneros',
                'descricao' => 'Leu livros de 3 categorias diferentes.',
                'icone' => 'ph-compass',
                'ativo' => $categoriasLidas >= 3,
                'cor' => 'amber',
            ],
            [
                'titulo' => 'Ritmo do mês',
                'descricao' => 'Concluiu leitura neste mês.',
                'icone' => 'ph-calendar-check',
                'ativo' => $leiturasNoMes >= 1,
                'cor' => 'blue',
            ],
        ]);

        $jaLidosIds = $emprestimos->pluck('livro_id')->filter();
        $favoritosIds = $favoritos->pluck('id');
        $categoriasBase = $categoriasPreferidas->pluck('categoria')
            ->merge($favoritos->pluck('categoria'))
            ->filter()
            ->unique()
            ->values();

        $sugestoes = Livros::with('autor')
            ->whereNotIn('id', $jaLidosIds->merge($favoritosIds)->unique())
            ->when($categoriasBase->isNotEmpty(), fn ($query) => $query->whereIn('categoria', $categoriasBase))
            ->orderByDesc('e_bestseller')
            ->latest()
            ->take(4)
            ->get();

        if ($sugestoes->isEmpty()) {
            $sugestoes = Livros::with('autor')
                ->whereNotIn('id', $jaLidosIds->merge($favoritosIds)->unique())
                ->orderByDesc('e_bestseller')
                ->latest()
                ->take(4)
                ->get();
        }

        return view('membros.minha-biblioteca', compact(
            'membro',
            'emprestimos',
            'ativos',
            'ativosComProgresso',
            'historicoRecente',
            'concluidos',
            'atrasados',
            'multasPendentes',
            'reservas',
            'favoritos',
            'categoriasPreferidas',
            'autoresPreferidos',
            'leiturasNoMes',
            'categoriasLidas',
            'totalConcluidos',
            'badges',
            'sugestoes'
        ));
    }

    public function situacao()
    {
        Emprestimos::expirarRetiradasPendentes();

        $membro = auth()->guard('membro')->user();

        $emprestimos = Emprestimos::with(['livro.autor', 'pagamentos'])
            ->where('membro_id', $membro->id)
            ->latest()
            ->get();

        $ativos = $emprestimos
            ->whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
            ->sortBy('data_devolucao_prevista')
            ->values();

        $solicitacoes = $emprestimos
            ->whereIn('status', [Emprestimos::STATUS_SOLICITADO, Emprestimos::STATUS_APROVADO])
            ->values();

        $atrasados = $emprestimos
            ->filter(fn (Emprestimos $emprestimo) => $emprestimo->isAtrasado())
            ->values();

        $multasPendentes = $emprestimos
            ->filter(fn (Emprestimos $emprestimo) => $emprestimo->multaPendente())
            ->values();

        $reservasAtivas = Schema::hasTable('reservas')
            ? Reserva::with('livro.autor')
                ->where('membro_id', $membro->id)
                ->where('status', Reserva::STATUS_ATIVA)
                ->latest()
                ->get()
                ->map(function (Reserva $reserva) {
                    $reserva->posicao_fila = Reserva::ativas()
                        ->where('livro_id', $reserva->livro_id)
                        ->where('created_at', '<=', $reserva->created_at)
                        ->count();

                    return $reserva;
                })
            : collect();

        $favoritos = Schema::hasTable('favoritos')
            ? $membro->livrosFavoritos()
                ->with('autor')
                ->orderByPivot('created_at', 'desc')
                ->take(4)
                ->get()
            : collect();

        $notificacoes = $membro->notifications()
            ->latest()
            ->take(5)
            ->get();

        $proximoPrazo = $ativos->first();
        $totalMultas = $multasPendentes->sum('valor_multa');
        $temPendencia = $atrasados->isNotEmpty() || $multasPendentes->isNotEmpty();

        $situacao = $temPendencia
            ? [
                'titulo' => 'Atenção necessária',
                'descricao' => 'Há atraso ou multa pendente para regularizar.',
                'icone' => 'ph-warning-circle',
                'classes' => 'border-red-200 bg-red-50 text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200',
            ]
            : [
                'titulo' => 'Tudo em dia',
                'descricao' => 'Nenhuma pendência aberta no momento.',
                'icone' => 'ph-shield-check',
                'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200',
            ];

        return view('membros.situacao', compact(
            'membro',
            'emprestimos',
            'ativos',
            'solicitacoes',
            'atrasados',
            'multasPendentes',
            'reservasAtivas',
            'favoritos',
            'notificacoes',
            'proximoPrazo',
            'totalMultas',
            'situacao'
        ));
    }
}
