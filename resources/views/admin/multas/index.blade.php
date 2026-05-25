<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center gap-1 shrink-0">
                    <i class="ph ph-library text-4xl text-[#1E3A8A] dark:text-blue-400"></i>
                    <div class="text-center text-[11px] font-black leading-tight tracking-tight">
                        <span class="text-[#1E3A8A] dark:text-blue-400">BIBLIO</span><br>
                        <span class="text-[#F59E0B]">TECH</span>
                    </div>
                </a>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-red-700 dark:text-red-300">Administração</p>
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Painel de multas</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pendências, arrecadação e regularização</p>
                </div>
            </div>

            <button type="button" @click="dark = !dark" class="h-10 w-10 rounded-md border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10" aria-label="Alternar tema">
                <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
            </button>
        </div>
    </x-slot>

    <div class="-mx-4 min-h-screen bg-gradient-to-b from-slate-100 via-blue-50 to-slate-100 px-4 py-8 dark:from-[#0f172a] dark:via-[#0f172a] dark:to-[#0b1120] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="multas-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#multas-dots)"/>
            </svg>
            <i class="ph ph-currency-circle-dollar absolute left-[7%] top-[14%] text-[42px] text-red-500/15 dark:text-red-300/10"></i>
            <i class="ph ph-receipt absolute right-[12%] top-[22%] text-[38px] text-amber-500/20 dark:text-amber-300/10"></i>
            <i class="ph ph-hand-coins absolute right-[20%] bottom-[18%] text-[46px] text-blue-800/10 dark:text-blue-300/10"></i>
        </div>

        <main class="relative z-10 mx-auto max-w-7xl space-y-6">
            <div class="flex flex-wrap justify-end gap-2">
                <a href="{{ route('admin.multas.pdf', request()->query()) }}" class="inline-flex h-10 items-center gap-2 rounded-md bg-[#F59E0B] px-4 text-[11px] font-black uppercase tracking-widest text-slate-950 transition hover:bg-amber-400">
                    <i class="ph ph-file-pdf"></i>
                    PDF
                </a>
                <a href="{{ route('admin.multas.csv', request()->query()) }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-file-csv"></i>
                    CSV
                </a>
            </div>

            <section class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-md border border-red-200 bg-red-50 p-4 shadow-sm dark:border-red-500/30 dark:bg-red-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-red-700 dark:text-red-300">Pendente</p>
                        <i class="ph ph-warning-circle text-xl text-red-600 dark:text-red-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-red-800 dark:text-red-200">R$ {{ number_format($metricas['total_pendente'], 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-red-700/80 dark:text-red-100/70">{{ $metricas['multas_pendentes'] }} multa{{ $metricas['multas_pendentes'] === 1 ? '' : 's' }} em aberto</p>
                </div>

                <div class="rounded-md border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Arrecadado</p>
                        <i class="ph ph-check-circle text-xl text-emerald-600 dark:text-emerald-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-emerald-800 dark:text-emerald-200">R$ {{ number_format($metricas['total_arrecadado'], 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-emerald-700/80 dark:text-emerald-100/70">{{ $metricas['multas_regularizadas'] }} regularizada{{ $metricas['multas_regularizadas'] === 1 ? '' : 's' }}</p>
                </div>

                <div class="rounded-md border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-amber-700 dark:text-amber-300">Inadimplentes</p>
                        <i class="ph ph-users-three text-xl text-amber-600 dark:text-amber-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-amber-800 dark:text-amber-200">{{ $metricas['membros_inadimplentes'] }}</p>
                    <p class="mt-1 text-xs text-amber-700/80 dark:text-amber-100/70">membro{{ $metricas['membros_inadimplentes'] === 1 ? '' : 's' }} com pendência</p>
                </div>

                <div class="rounded-md border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">Maior multa</p>
                        <i class="ph ph-chart-line-up text-xl text-blue-600 dark:text-blue-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-blue-800 dark:text-blue-200">R$ {{ number_format($metricas['maior_multa'], 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-blue-700/80 dark:text-blue-100/70">maior valor registrado</p>
                </div>
            </section>

            <section class="rounded-md border border-slate-200 bg-white/95 p-4 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                <form method="GET" class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1.2fr)_160px_150px_150px_150px_auto_auto] lg:items-end">
                    <div>
                        <label for="membro" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Membro</label>
                        <input id="membro" name="membro" value="{{ $membroBusca }}" placeholder="Nome, email, CPF ou carteirinha" class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200">
                    </div>
                    <div>
                        <label for="status" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Status</label>
                        <select id="status" name="status" class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200">
                            <option value="todas" @selected($status === 'todas')>Todas</option>
                            <option value="pendentes" @selected($status === 'pendentes')>Pendentes</option>
                            <option value="regularizadas" @selected($status === 'regularizadas')>Regularizadas</option>
                        </select>
                    </div>
                    <div>
                        <label for="valor_minimo" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Valor mín.</label>
                        <input id="valor_minimo" name="valor_minimo" value="{{ $valorMinimo }}" type="number" min="0" step="0.01" class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200">
                    </div>
                    <div>
                        <label for="inicio" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Início</label>
                        <input id="inicio" name="inicio" value="{{ $inicio?->toDateString() }}" type="date" min="2000-01-01" max="{{ today()->toDateString() }}" class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200">
                    </div>
                    <div>
                        <label for="fim" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Fim</label>
                        <input id="fim" name="fim" value="{{ $fim?->toDateString() }}" type="date" min="2000-01-01" max="{{ today()->toDateString() }}" class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200">
                    </div>
                    <button class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                        <i class="ph ph-funnel"></i>
                        Filtrar
                    </button>
                    <a href="{{ route('admin.multas.index') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                        <i class="ph ph-x"></i>
                        Limpar
                    </a>
                </form>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
                <div class="overflow-hidden rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-red-700 dark:text-red-300">Controle</p>
                            <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Multas encontradas</h2>
                        </div>
                        <span class="rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-black text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">{{ $multas->total() }}</span>
                    </div>

                    <div class="overflow-x-auto overscroll-x-contain pb-2">
                        <table class="min-w-[980px] w-full text-left">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:bg-[#080d14] dark:text-slate-400">
                                <tr>
                                    <th class="px-5 py-3">Membro</th>
                                    <th class="px-5 py-3">Livro</th>
                                    <th class="px-5 py-3">Devolução</th>
                                    <th class="px-5 py-3">Valor</th>
                                    <th class="px-5 py-3">Situação</th>
                                    <th class="px-5 py-3 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-white/10">
                                @forelse($multas as $multa)
                                    @php
                                        $pendente = $multa->multaPendente();
                                        $diasAtraso = $multa->data_devolucao_prevista && $multa->data_devolucao_real
                                            ? max(0, (int) $multa->data_devolucao_prevista->diffInDays($multa->data_devolucao_real, false))
                                            : null;
                                    @endphp
                                    <tr class="align-top">
                                        <td class="px-5 py-4">
                                            <p class="font-bold text-slate-950 dark:text-white">{{ $multa->membro?->nome ?? 'Membro removido' }}</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $multa->membro?->email ?? 'Sem email' }}</p>
                                            <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $multa->membro?->numero_carteirinha ?? 'Sem carteirinha' }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="font-bold text-slate-900 dark:text-white">{{ $multa->livro?->titulo ?? 'Livro removido' }}</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $multa->livro?->autor?->nome ?? 'Autor não informado' }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $multa->data_devolucao_real?->format('d/m/Y') ?? 'Sem data' }}</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                Prazo {{ $multa->data_devolucao_prevista?->format('d/m/Y') ?? '--' }}
                                                @if($diasAtraso)
                                                    · {{ $diasAtraso }} dia{{ $diasAtraso === 1 ? '' : 's' }}
                                                @endif
                                            </p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="text-lg font-black {{ $pendente ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">R$ {{ number_format($multa->valor_multa, 2, ',', '.') }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            @if($pendente)
                                                <span class="inline-flex rounded-md border border-red-200 bg-red-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">Pendente</span>
                                            @else
                                                <span class="inline-flex rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">Regularizada</span>
                                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $multa->multa_paga_em?->format('d/m/Y H:i') }}</p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                @if($multa->membro)
                                                    <a href="{{ route('admin.membros.show', $multa->membro_id) }}" class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                                        <i class="ph ph-user"></i>
                                                        Perfil
                                                    </a>
                                                @endif
                                                @if($pendente)
                                                    <form action="{{ route('admin.emprestimos.regularizar-multa', $multa->id) }}" method="POST" data-confirm="loan" data-title="Regularizar multa?" data-text="A multa será marcada como paga e o membro poderá solicitar novos empréstimos.">
                                                        @csrf
                                                        <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-md bg-emerald-600 px-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-emerald-700">
                                                            <i class="ph ph-check-circle"></i>
                                                            Regularizar
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                            Nenhuma multa encontrada com os filtros atuais.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-200 px-5 py-4 dark:border-white/10">
                        {{ $multas->links() }}
                    </div>
                </div>

                <aside class="space-y-6">
                    <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Ranking</p>
                                <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Maiores devedores</h2>
                            </div>
                            <i class="ph ph-chart-bar text-2xl text-amber-600 dark:text-amber-300"></i>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse($maioresDevedores as $index => $devedor)
                                <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-slate-950 dark:text-white">{{ $index + 1 }}. {{ $devedor->membro?->nome ?? 'Membro removido' }}</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $devedor->multas }} multa{{ $devedor->multas == 1 ? '' : 's' }} pendente{{ $devedor->multas == 1 ? '' : 's' }}</p>
                                        </div>
                                        <p class="shrink-0 text-sm font-black text-red-700 dark:text-red-300">R$ {{ number_format($devedor->total, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-md border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">
                                    Nenhum membro com multa pendente.
                                </p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-md border border-blue-200 bg-blue-50 p-5 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Regra aplicada</p>
                        <h2 class="mt-2 font-serif text-xl font-black text-blue-950 dark:text-blue-100">R$ {{ number_format(\App\Models\Emprestimos::VALOR_MULTA_DIARIA, 2, ',', '.') }} por dia</h2>
                        <p class="mt-2 text-sm leading-relaxed text-blue-800 dark:text-blue-100/80">
                            O sistema calcula multa quando a devolução acontece após a data prevista. Enquanto a multa estiver pendente, o membro fica impedido de solicitar novos empréstimos.
                        </p>
                    </section>
                </aside>
            </section>
        </main>
    </div>
</x-app-layout>
