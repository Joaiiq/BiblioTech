<x-app-layout>
    @php
        $totalLidos = $totalConcluidos;
        $multaAberta = $multasPendentes->sum('valor_multa');
        $proximoPrazo = $ativos->first()?->data_devolucao_prevista;
        $categoriaFavorita = $categoriasPreferidas->first();
        $autorFavorito = $autoresPreferidos->first();
    @endphp

    <x-slot name="header">
        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center gap-1 shrink-0">
                    <i class="ph ph-library text-4xl text-[#1E3A8A] dark:text-blue-400"></i>
                    <div class="text-center text-[11px] font-black leading-tight tracking-tight">
                        <span class="text-[#1E3A8A] dark:text-blue-400">BIBLIO</span><br>
                        <span class="text-[#F59E0B]">TECH</span>
                    </div>
                </a>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Área do leitor</p>
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Minha biblioteca</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tudo sobre seus empréstimos, reservas e favoritos em um lugar</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-arrow-left"></i>
                    Voltar ao painel
                </a>
                <button type="button" @click="dark = !dark" class="h-10 w-10 rounded-md border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10" aria-label="Alternar tema">
                    <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="-mx-4 min-h-screen bg-gradient-to-b from-slate-100 via-blue-50 to-slate-100 px-4 py-8 dark:from-[#0f172a] dark:via-[#0f172a] dark:to-[#0b1120] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="reader-hub-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#reader-hub-dots)"/>
            </svg>
            <i class="ph ph-books absolute left-[5%] top-[12%] text-[44px] text-amber-500/20 dark:text-amber-300/10"></i>
            <i class="ph ph-bookmark-simple absolute right-[10%] top-[20%] text-[36px] text-blue-800/10 dark:text-blue-300/10"></i>
            <i class="ph ph-clock-countdown absolute right-[18%] bottom-[18%] text-[44px] text-amber-500/15 dark:text-amber-300/10"></i>
        </div>

        <main class="relative z-10 mx-auto max-w-7xl space-y-6">
            <section class="overflow-hidden rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                <div class="grid grid-cols-1 gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[.16em] text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                            <i class="ph ph-user-circle"></i>
                            {{ $membro->nome }}
                        </span>
                        <h2 class="mt-3 max-w-3xl font-serif text-3xl font-black leading-tight text-slate-950 dark:text-white md:text-4xl">
                            Sua leitura, seus prazos e sua próxima escolha em uma só estante.
                        </h2>
                        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            Use este painel para acompanhar livros em uso, reservas, favoritos e sugestões baseadas no que você já leu ou salvou.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('emprestimos.historico') }}" class="inline-flex h-11 items-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                <i class="ph ph-clock-countdown"></i>
                                Ver empréstimos
                            </a>
                            <a href="{{ route('favoritos.index') }}" class="inline-flex h-11 items-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-4 text-[11px] font-black uppercase tracking-widest text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20">
                                <i class="ph ph-heart"></i>
                                Ver favoritos
                            </a>
                            <a href="{{ route('membros.carteirinha') }}" class="inline-flex h-11 items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                <i class="ph ph-identification-card"></i>
                                Carteirinha
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-md border border-blue-200 bg-blue-50 p-3 dark:border-blue-500/20 dark:bg-blue-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-blue-700 dark:text-blue-300">Em uso</p>
                            <p class="mt-1 text-3xl font-black text-blue-800 dark:text-blue-300">{{ $ativos->count() }}</p>
                        </div>
                        <div class="rounded-md border border-red-200 bg-red-50 p-3 dark:border-red-500/20 dark:bg-red-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-red-600 dark:text-red-300">Atrasos</p>
                            <p class="mt-1 text-3xl font-black text-red-700 dark:text-red-300">{{ $atrasados->count() }}</p>
                        </div>
                        <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-amber-700 dark:text-amber-300">Favoritos</p>
                            <p class="mt-1 text-3xl font-black text-amber-800 dark:text-amber-300">{{ $favoritos->count() }}</p>
                        </div>
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Reservas</p>
                            <p class="mt-1 text-3xl font-black text-emerald-700 dark:text-emerald-300">{{ $reservas->where('status', \App\Models\Reserva::STATUS_ATIVA)->count() }}</p>
                        </div>
                    </div>
                </div>
            </section>

            @if($proximoPrazo || $multaAberta > 0)
                <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="rounded-md border {{ $multaAberta > 0 ? 'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' : 'border-slate-200 bg-white/95 dark:border-white/10 dark:bg-[#0d1420]/95' }} p-5 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] {{ $multaAberta > 0 ? 'text-red-700 dark:text-red-300' : 'text-slate-500 dark:text-slate-400' }}">Situação</p>
                        <h3 class="mt-2 font-serif text-2xl font-black {{ $multaAberta > 0 ? 'text-red-800 dark:text-red-200' : 'text-slate-950 dark:text-white' }}">
                            {{ $multaAberta > 0 ? 'Regularize multas pendentes' : 'Tudo certo por enquanto' }}
                        </h3>
                        <p class="mt-1 text-sm {{ $multaAberta > 0 ? 'text-red-700 dark:text-red-200' : 'text-slate-500 dark:text-slate-400' }}">
                            {{ $multaAberta > 0 ? 'Valor em aberto: R$ ' . number_format($multaAberta, 2, ',', '.') : 'Nenhuma multa pendente registrada no momento.' }}
                        </p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Próximo prazo</p>
                        <h3 class="mt-2 font-serif text-2xl font-black text-slate-950 dark:text-white">
                            {{ $proximoPrazo ? $proximoPrazo->format('d/m/Y') : 'Sem prazo ativo' }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $proximoPrazo ? $proximoPrazo->diffForHumans() : 'Nenhum livro em uso agora.' }}
                        </p>
                    </div>
                </section>
            @endif

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,.95fr)_minmax(0,1.05fr)]">
                <div class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="mb-5 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Perfil de leitura</p>
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Seu ranking</h3>
                        </div>
                        <i class="ph ph-chart-bar text-2xl text-emerald-600 dark:text-emerald-300"></i>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                            <p class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400">Concluídos</p>
                            <p class="mt-1 text-3xl font-black text-slate-950 dark:text-white">{{ $totalLidos }}</p>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                            <p class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400">Neste mês</p>
                            <p class="mt-1 text-3xl font-black text-slate-950 dark:text-white">{{ $leiturasNoMes }}</p>
                        </div>
                        <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-amber-700 dark:text-amber-300">Categoria top</p>
                            <p class="mt-1 truncate text-sm font-black text-amber-900 dark:text-amber-200">{{ $categoriaFavorita->categoria ?? 'Sem dados' }}</p>
                        </div>
                        <div class="rounded-md border border-blue-200 bg-blue-50 p-3 dark:border-blue-500/20 dark:bg-blue-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-blue-700 dark:text-blue-300">Autor top</p>
                            <p class="mt-1 truncate text-sm font-black text-blue-900 dark:text-blue-200">{{ $autorFavorito->nome ?? 'Sem dados' }}</p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2">
                        @forelse($autoresPreferidos as $index => $autor)
                            <div class="flex items-center justify-between gap-3 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 dark:border-white/10 dark:bg-white/[.03]">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[#1E3A8A] text-[10px] font-black text-white">{{ $index + 1 }}</span>
                                    <span class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $autor->nome }}</span>
                                </span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $autor->total }} leitura{{ $autor->total == 1 ? '' : 's' }}</span>
                            </div>
                        @empty
                            <p class="rounded-md border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">O ranking aparece quando houver devoluções concluídas.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="mb-5 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Conquistas</p>
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Badges do leitor</h3>
                        </div>
                        <span class="rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                            {{ $badges->where('ativo', true)->count() }}/{{ $badges->count() }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($badges as $badge)
                            @php
                                $activeClasses = match ($badge['cor']) {
                                    'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200',
                                    'amber' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200',
                                    default => 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200',
                                };
                            @endphp
                            <div class="rounded-md border p-3 {{ $badge['ativo'] ? $activeClasses : 'border-slate-200 bg-slate-50 text-slate-400 opacity-75 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-500' }}">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-white/70 text-xl dark:bg-[#0d1420]/70">
                                        <i class="ph {{ $badge['icone'] }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-black">{{ $badge['titulo'] }}</p>
                                        <p class="mt-1 text-xs leading-relaxed opacity-80">{{ $badge['descricao'] }}</p>
                                        <p class="mt-2 text-[9px] font-black uppercase tracking-widest">{{ $badge['ativo'] ? 'Desbloqueado' : 'Em progresso' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(360px,.8fr)]">
                <div class="space-y-6">
                    <section class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Agora</p>
                                <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Empréstimos ativos</h3>
                            </div>
                            <a href="{{ route('emprestimos.historico') }}" class="text-[10px] font-black uppercase tracking-widest text-blue-700 hover:text-blue-900 dark:text-blue-300 dark:hover:text-blue-200">Histórico</a>
                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 lg:grid-cols-2">
                            @forelse($ativosComProgresso as $item)
                                @php
                                    $emprestimo = $item['emprestimo'];
                                    $atrasado = $emprestimo->isAtrasado();
                                    $dias = $item['dias_restantes'];
                                    $progresso = $item['progresso'];
                                    $risco = $item['risco'];
                                    $riskClasses = match ($risco) {
                                        'atrasado' => [
                                            'card' => 'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10',
                                            'bar' => 'bg-red-500',
                                            'badge' => 'border-red-200 bg-red-100 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300',
                                            'text' => 'text-red-700 dark:text-red-300',
                                        ],
                                        'risco' => [
                                            'card' => 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10',
                                            'bar' => 'bg-amber-500',
                                            'badge' => 'border-amber-200 bg-amber-100 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300',
                                            'text' => 'text-amber-800 dark:text-amber-300',
                                        ],
                                        default => [
                                            'card' => 'border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/[.03]',
                                            'bar' => 'bg-emerald-500',
                                            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300',
                                            'text' => 'text-slate-600 dark:text-slate-400',
                                        ],
                                    };
                                @endphp
                                <article class="rounded-md border p-4 {{ $riskClasses['card'] }}">
                                    <div class="flex min-w-0 gap-4">
                                        <div class="h-24 w-16 shrink-0 overflow-hidden rounded-md bg-white ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10">
                                            @if($emprestimo->livro?->capa)
                                                <img src="{{ asset('storage/' . $emprestimo->livro->capa) }}" alt="{{ $emprestimo->livro?->titulo }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <i class="ph ph-book text-slate-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-2">
                                                <h4 class="line-clamp-2 text-sm font-black text-slate-950 dark:text-white">{{ $emprestimo->livro?->titulo ?? 'Livro removido' }}</h4>
                                                <span class="shrink-0 rounded-md border px-2 py-1 text-[9px] font-black uppercase tracking-widest {{ $riskClasses['badge'] }}">
                                                    {{ $atrasado ? 'Vencido' : ($risco === 'risco' ? 'Atenção' : 'No prazo') }}
                                                </span>
                                            </div>
                                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $emprestimo->livro?->autor?->nome ?? 'Autor não informado' }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="mb-2 flex items-center justify-between gap-3">
                                            <p class="text-[10px] font-black uppercase tracking-widest {{ $riskClasses['text'] }}">
                                                {{ $atrasado ? 'Atrasado há ' . abs($dias) . ' dia' . (abs($dias) === 1 ? '' : 's') : 'Faltam ' . $dias . ' dia' . ($dias === 1 ? '' : 's') }}
                                            </p>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $progresso }}%</p>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-white ring-1 ring-slate-200 dark:bg-[#0d1420] dark:ring-white/10">
                                            <div class="h-full rounded-full {{ $riskClasses['bar'] }}" style="width: {{ $progresso }}%"></div>
                                        </div>
                                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                                Prazo: <strong class="text-slate-800 dark:text-slate-200">{{ $emprestimo->data_devolucao_prevista?->format('d/m/Y') ?? '--' }}</strong>
                                            </span>
                                            <a href="{{ route('livros.show', $emprestimo->livro_id) }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-slate-200 bg-white px-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                                <i class="ph ph-eye"></i>
                                                Ver livro
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-md border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400 lg:col-span-2">Nenhum empréstimo ativo agora.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Para você</p>
                                <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Sugestões de próxima leitura</h3>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @forelse($sugestoes as $livro)
                                <a href="{{ route('livros.show', $livro->id) }}" class="group flex gap-3 rounded-md border border-slate-200 bg-slate-50 p-3 transition hover:border-blue-300 hover:bg-blue-50 dark:border-white/10 dark:bg-white/[.03] dark:hover:border-blue-500/40 dark:hover:bg-blue-500/10">
                                    <div class="h-24 w-16 shrink-0 overflow-hidden rounded bg-slate-200 dark:bg-white/10">
                                        @if($livro->capa)
                                            <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center"><i class="ph ph-book-open text-slate-400"></i></div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">{{ $livro->categoria ?? 'Acervo' }}</p>
                                        <h4 class="mt-1 line-clamp-2 text-sm font-black text-slate-950 group-hover:text-blue-800 dark:text-white dark:group-hover:text-blue-300">{{ $livro->titulo }}</h4>
                                        <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $livro->autor->nome ?? 'Autor não informado' }}</p>
                                    </div>
                                </a>
                            @empty
                                <p class="rounded-md border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">As sugestões aparecem quando houver mais livros no acervo.</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="rounded-md border border-amber-200 bg-white/95 p-5 shadow-sm dark:border-amber-500/20 dark:bg-[#0d1420]/95">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Quero ler</p>
                                <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Favoritos recentes</h3>
                            </div>
                            <a href="{{ route('favoritos.index') }}" class="text-[10px] font-black uppercase tracking-widest text-amber-800 hover:text-amber-900 dark:text-amber-300">Todos</a>
                        </div>
                        <div class="space-y-2">
                            @forelse($favoritos as $livro)
                                <a href="{{ route('livros.show', $livro->id) }}" class="flex items-center gap-3 rounded-md border border-slate-200 bg-slate-50 p-2 transition hover:border-amber-300 hover:bg-amber-50 dark:border-white/10 dark:bg-white/[.03] dark:hover:border-amber-500/40 dark:hover:bg-amber-500/10">
                                    <div class="h-14 w-10 shrink-0 overflow-hidden rounded bg-slate-200 dark:bg-white/10">
                                        @if($livro->capa)
                                            <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center"><i class="ph ph-book text-slate-400"></i></div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $livro->titulo }}</p>
                                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $livro->autor->nome ?? 'Autor não informado' }}</p>
                                    </div>
                                </a>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum favorito salvo ainda.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                        <div class="mb-4">
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Perfil</p>
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Categorias preferidas</h3>
                        </div>
                        <div class="space-y-2">
                            @forelse($categoriasPreferidas as $categoria)
                                <div class="flex items-center justify-between gap-3 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 dark:border-white/10 dark:bg-white/[.03]">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $categoria->categoria }}</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $categoria->total }} leitura{{ $categoria->total == 1 ? '' : 's' }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Ainda não há leitura suficiente para traçar preferências.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Fila</p>
                                <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Reservas</h3>
                            </div>
                            <a href="{{ route('emprestimos.historico') }}" class="text-[10px] font-black uppercase tracking-widest text-blue-700 hover:text-blue-900 dark:text-blue-300">Ver</a>
                        </div>
                        <div class="space-y-2">
                            @forelse($reservas as $reserva)
                                <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $reserva->livro?->titulo ?? 'Livro removido' }}</p>
                                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $reserva->livro?->autor?->nome ?? 'Autor não informado' }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-md border px-2 py-1 text-[9px] font-black uppercase tracking-widest {{ $reserva->status === \App\Models\Reserva::STATUS_ATIVA ? 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300' : 'border-slate-200 bg-white text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-300' }}">
                                            {{ $reserva->status === \App\Models\Reserva::STATUS_ATIVA ? 'Fila ' . ($reserva->posicao_fila ?? '--') : $reserva->status }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Nenhuma reserva registrada.</p>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </section>
        </main>
    </div>
</x-app-layout>
