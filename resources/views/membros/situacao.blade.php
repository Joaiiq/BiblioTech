<x-app-layout>
    @php
        \Carbon\Carbon::setLocale('pt_BR');
        $unreadCount = $membro->unreadNotifications()->count();
        $proximoLivro = $proximoPrazo?->livro;
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
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Área do membro</p>
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Minha situação</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pendências, prazos, reservas e avisos</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('membros.biblioteca') }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-books"></i>
                    Biblioteca
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
                    <pattern id="situacao-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#situacao-dots)"/>
            </svg>
            <i class="ph ph-clipboard-text absolute left-[5%] top-[14%] text-[44px] text-emerald-500/15 dark:text-emerald-300/10"></i>
            <i class="ph ph-bell-ringing absolute right-[12%] top-[20%] text-[38px] text-amber-500/20 dark:text-amber-300/10"></i>
            <i class="ph ph-books absolute right-[20%] bottom-[16%] text-[48px] text-blue-800/10 dark:text-blue-300/10"></i>
        </div>

        <main class="relative z-10 mx-auto max-w-7xl space-y-6">
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                <div class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95 sm:p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <span class="inline-flex items-center gap-2 rounded-md border px-2.5 py-1 text-[10px] font-black uppercase tracking-[.16em] {{ $situacao['classes'] }}">
                                <i class="ph {{ $situacao['icone'] }}"></i>
                                {{ $situacao['titulo'] }}
                            </span>
                            <h2 class="mt-4 max-w-3xl font-serif text-3xl font-black leading-tight text-slate-950 dark:text-white md:text-4xl">
                                {{ $membro->nome }}, aqui está o panorama da sua conta.
                            </h2>
                            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                {{ $situacao['descricao'] }} Acompanhe prazos, fila de reserva e mensagens importantes sem precisar caçar informação em várias telas.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 lg:w-[360px]">
                            <div class="rounded-md border border-blue-200 bg-blue-50 p-3 dark:border-blue-500/20 dark:bg-blue-500/10">
                                <p class="text-[10px] uppercase tracking-widest text-blue-700 dark:text-blue-300">Em uso</p>
                                <p class="mt-1 text-3xl font-black text-blue-800 dark:text-blue-300">{{ $ativos->count() }}</p>
                            </div>
                            <div class="rounded-md border border-red-200 bg-red-50 p-3 dark:border-red-500/20 dark:bg-red-500/10">
                                <p class="text-[10px] uppercase tracking-widest text-red-600 dark:text-red-300">Atrasos</p>
                                <p class="mt-1 text-3xl font-black text-red-700 dark:text-red-300">{{ $atrasados->count() }}</p>
                            </div>
                            <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                                <p class="text-[10px] uppercase tracking-widest text-amber-700 dark:text-amber-300">Multas</p>
                                <p class="mt-1 text-2xl font-black text-amber-800 dark:text-amber-300">R$ {{ number_format($totalMultas, 2, ',', '.') }}</p>
                            </div>
                            <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                <p class="text-[10px] uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Avisos</p>
                                <p class="mt-1 text-3xl font-black text-emerald-700 dark:text-emerald-300">{{ $unreadCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Próximo prazo</p>
                    @if($proximoPrazo)
                        <div class="mt-4 flex gap-4">
                            <div class="h-24 w-16 shrink-0 overflow-hidden rounded-md bg-slate-100 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10">
                                @if($proximoLivro?->capa)
                                    <img src="{{ asset('storage/' . $proximoLivro->capa) }}" alt="{{ $proximoLivro->titulo }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <i class="ph ph-book text-slate-400"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h3 class="line-clamp-2 font-serif text-lg font-black text-slate-950 dark:text-white">{{ $proximoLivro?->titulo ?? 'Livro removido' }}</h3>
                                <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $proximoLivro?->autor?->nome ?? 'Autor não informado' }}</p>
                                <p class="mt-3 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-500">Devolver até</p>
                                <p class="text-xl font-black {{ $proximoPrazo->isAtrasado() ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                                    {{ $proximoPrazo->data_devolucao_prevista?->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 rounded-md border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">
                            Nenhum livro em uso agora.
                        </div>
                    @endif
                </aside>
            </section>

            @if($atrasados->isNotEmpty() || $multasPendentes->isNotEmpty() || $solicitacoes->isNotEmpty())
                <section class="rounded-md border border-amber-200 bg-white/95 shadow-sm dark:border-amber-500/20 dark:bg-[#0d1420]/95">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Precisa de ação</p>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Itens que merecem atenção</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 lg:grid-cols-3">
                        @foreach($atrasados->take(3) as $emprestimo)
                            <article class="rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-500/30 dark:bg-red-500/10">
                                <p class="text-[10px] font-black uppercase tracking-widest text-red-700 dark:text-red-300">Atrasado</p>
                                <h4 class="mt-2 line-clamp-2 font-bold text-red-950 dark:text-red-100">{{ $emprestimo->livro?->titulo ?? 'Livro removido' }}</h4>
                                <p class="mt-1 text-xs text-red-700 dark:text-red-200">Venceu {{ $emprestimo->data_devolucao_prevista?->diffForHumans() }}</p>
                            </article>
                        @endforeach

                        @foreach($multasPendentes->take(3) as $emprestimo)
                            @php
                                $pagamentoPendente = $emprestimo->pagamentos?->firstWhere('status', \App\Models\Pagamento::STATUS_PENDENTE);
                            @endphp
                            <article class="rounded-md border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                                <p class="text-[10px] font-black uppercase tracking-widest text-amber-700 dark:text-amber-300">Multa aberta</p>
                                <h4 class="mt-2 line-clamp-2 font-bold text-amber-950 dark:text-amber-100">{{ $emprestimo->livro?->titulo ?? 'Livro removido' }}</h4>
                                <p class="mt-1 text-xs text-amber-800 dark:text-amber-200">R$ {{ number_format($emprestimo->valor_multa, 2, ',', '.') }}</p>
                                <a href="{{ route('pagamentos.checkout', $emprestimo) }}" class="mt-4 inline-flex h-9 items-center gap-2 rounded-md {{ $pagamentoPendente ? 'bg-slate-200 text-slate-700 dark:bg-white/10 dark:text-slate-300' : 'bg-amber-500 text-slate-950 hover:bg-amber-400' }} px-3 text-[10px] font-black uppercase tracking-widest transition">
                                    <i class="ph ph-credit-card"></i>
                                    {{ $pagamentoPendente ? 'Em análise' : 'Pagar agora' }}
                                </a>
                            </article>
                        @endforeach

                        @foreach($solicitacoes->take(3) as $emprestimo)
                            <article class="rounded-md border border-blue-200 bg-blue-50 p-4 dark:border-blue-500/30 dark:bg-blue-500/10">
                                <p class="text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">Solicitação</p>
                                <h4 class="mt-2 line-clamp-2 font-bold text-blue-950 dark:text-blue-100">{{ $emprestimo->livro?->titulo ?? 'Livro removido' }}</h4>
                                <p class="mt-1 text-xs text-blue-800 dark:text-blue-200">{{ str_replace('_', ' ', ucfirst($emprestimo->status)) }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(360px,.9fr)]">
                <div class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Em andamento</p>
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Livros com você</h3>
                        </div>
                        <a href="{{ route('emprestimos.historico') }}" class="text-[10px] font-black uppercase tracking-widest text-blue-700 hover:text-blue-900 dark:text-blue-300 dark:hover:text-blue-200">Ver todos</a>
                    </div>
                    <div class="divide-y divide-slate-200 dark:divide-white/10">
                        @forelse($ativos->take(5) as $emprestimo)
                            <article class="flex gap-4 p-5">
                                <div class="h-20 w-14 shrink-0 overflow-hidden rounded-md bg-slate-100 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10">
                                    @if($emprestimo->livro?->capa)
                                        <img src="{{ asset('storage/' . $emprestimo->livro->capa) }}" alt="{{ $emprestimo->livro?->titulo }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center"><i class="ph ph-book text-slate-400"></i></div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="line-clamp-1 font-bold text-slate-950 dark:text-white">{{ $emprestimo->livro?->titulo ?? 'Livro removido' }}</h4>
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $emprestimo->livro?->autor?->nome ?? 'Autor não informado' }}</p>
                                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                        <span class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-300">
                                            Empréstimo: <strong class="text-slate-950 dark:text-white">{{ $emprestimo->data_emprestimo?->format('d/m') }}</strong>
                                        </span>
                                        <span class="rounded-md border {{ $emprestimo->isAtrasado() ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300' : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300' }} px-3 py-2">
                                            Prazo: <strong>{{ $emprestimo->data_devolucao_prevista?->format('d/m') }}</strong>
                                        </span>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="p-6 text-sm text-slate-500 dark:text-slate-400">Nenhum empréstimo ativo no momento.</div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-6">
                    <section class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Reservas</p>
                                <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Fila ativa</h3>
                            </div>
                            <span class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-black text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $reservasAtivas->count() }}</span>
                        </div>
                        <div class="divide-y divide-slate-200 dark:divide-white/10">
                            @forelse($reservasAtivas->take(4) as $reserva)
                                <div class="flex items-center justify-between gap-3 p-4">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-950 dark:text-white">{{ $reserva->livro?->titulo ?? 'Livro removido' }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Posição {{ $reserva->posicao_fila ?? '--' }} na fila</p>
                                    </div>
                                    <a href="{{ route('livros.show', $reserva->livro_id) }}" class="shrink-0 text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">Abrir</a>
                                </div>
                            @empty
                                <div class="p-5 text-sm text-slate-500 dark:text-slate-400">Nenhuma reserva ativa.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Favoritos</p>
                                <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Salvos recentemente</h3>
                            </div>
                            <a href="{{ route('favoritos.index') }}" class="text-[10px] font-black uppercase tracking-widest text-amber-700 dark:text-amber-300">Todos</a>
                        </div>
                        <div class="grid grid-cols-2 gap-3 p-4">
                            @forelse($favoritos as $livro)
                                <a href="{{ route('livros.show', $livro->id) }}" class="rounded-md border border-slate-200 bg-slate-50 p-3 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[.03] dark:hover:bg-white/10">
                                    <p class="line-clamp-2 text-sm font-bold text-slate-950 dark:text-white">{{ $livro->titulo }}</p>
                                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $livro->autor?->nome ?? 'Autor não informado' }}</p>
                                </a>
                            @empty
                                <div class="col-span-2 p-2 text-sm text-slate-500 dark:text-slate-400">Nenhum favorito salvo.</div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </section>

            <section class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Avisos</p>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Notificações recentes</h3>
                    </div>
                    <a href="{{ route('notifications.index') }}" class="text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">Central</a>
                </div>
                <div class="divide-y divide-slate-200 dark:divide-white/10">
                    @forelse($notificacoes as $notificacao)
                        <article class="flex items-start justify-between gap-4 p-5 {{ $notificacao->read_at ? '' : 'bg-blue-50/70 dark:bg-blue-500/5' }}">
                            <div class="min-w-0">
                                <p class="text-sm text-slate-800 dark:text-slate-200">{!! $notificacao->data['message'] ?? ($notificacao->data['title'] ?? 'Notificação') !!}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $notificacao->created_at->diffForHumans() }}</p>
                            </div>
                            @unless($notificacao->read_at)
                                <span class="h-2 w-2 shrink-0 rounded-full bg-blue-600"></span>
                            @endunless
                        </article>
                    @empty
                        <div class="p-6 text-sm text-slate-500 dark:text-slate-400">Sem notificações recentes.</div>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
</x-app-layout>
