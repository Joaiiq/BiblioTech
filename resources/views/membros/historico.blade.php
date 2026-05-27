<x-app-layout>
    @php
        \Carbon\Carbon::setLocale('pt_BR');

        $reservas = $reservas ?? collect();
        $totalEmprestimos = $emprestimos->count();
        $ativos = $emprestimos->filter(fn ($emp) => in_array($emp->status, \App\Models\Emprestimos::STATUS_EM_ANDAMENTO, true))->count();
        $atrasados = $emprestimos->filter(fn ($emp) => $emp->isAtrasado())->count();
        $concluidos = $emprestimos->filter(fn ($emp) => in_array($emp->status, [\App\Models\Emprestimos::STATUS_DEVOLVIDO, \App\Models\Emprestimos::STATUS_ENCERRADO], true))->count();
        $reservasAtivas = $reservas->where('status', \App\Models\Reserva::STATUS_ATIVA)->count();
        $reservasAtendidas = $reservas->where('status', \App\Models\Reserva::STATUS_ATENDIDA)->count();
        $reservasCanceladas = $reservas->where('status', \App\Models\Reserva::STATUS_CANCELADA)->count();
        $multaAberta = $emprestimos->filter(fn ($emp) => $emp->multaPendente())->sum('valor_multa');
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
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Minha circulação</p>
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Meus empréstimos</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Prazos, reservas, renovações e comprovantes</p>
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
                    <pattern id="history-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#history-dots)"/>
            </svg>
            <i class="ph ph-clock-countdown absolute left-[7%] top-[14%] text-[38px] text-amber-500/20 dark:text-amber-300/10"></i>
            <i class="ph ph-ticket absolute right-[9%] top-[18%] text-[36px] text-blue-800/10 dark:text-blue-300/10"></i>
            <i class="ph ph-bookmark-simple absolute right-[18%] bottom-[18%] text-[46px] text-amber-500/15 dark:text-amber-300/10"></i>
        </div>

        <main class="relative z-10 mx-auto max-w-7xl space-y-6">
            <section class="overflow-hidden rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                <div class="grid grid-cols-1 gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[.16em] text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                            <i class="ph ph-calendar-check"></i>
                            Acompanhamento
                        </span>
                        <h2 class="mt-3 max-w-3xl font-serif text-3xl font-black leading-tight text-slate-950 dark:text-white md:text-4xl">
                            Controle o que está com você, o que está em fila e o que já foi devolvido.
                        </h2>
                        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            Os prazos seguem {{ \App\Models\Emprestimos::PRAZO_LIVRO_COMUM_DIAS }} dias para livros comuns, {{ \App\Models\Emprestimos::PRAZO_BESTSELLER_DIAS }} dias para bestsellers e multa de R$ {{ number_format(\App\Models\Emprestimos::VALOR_MULTA_DIARIA, 2, ',', '.') }} por dia de atraso.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                            <p class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-500">Ativos</p>
                            <p class="mt-1 text-2xl font-black text-blue-700 dark:text-blue-300">{{ $ativos }}</p>
                        </div>
                        <div class="rounded-md border border-red-200 bg-red-50 p-3 dark:border-red-500/20 dark:bg-red-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-red-600 dark:text-red-300">Atrasos</p>
                            <p class="mt-1 text-2xl font-black text-red-700 dark:text-red-300">{{ $atrasados }}</p>
                        </div>
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Concluídos</p>
                            <p class="mt-1 text-2xl font-black text-emerald-700 dark:text-emerald-300">{{ $concluidos }}</p>
                        </div>
                        <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-amber-700 dark:text-amber-300">Multas</p>
                            <p class="mt-1 text-2xl font-black text-amber-800 dark:text-amber-300">R$ {{ number_format($multaAberta, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-md border border-slate-200 bg-white/95 p-4 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                    <div class="relative">
                        <i class="ph ph-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="history-search" type="text" placeholder="Buscar por livro, autor ou status..." class="h-11 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200">
                    </div>
                    <div class="flex gap-2 overflow-x-auto pb-1 lg:pb-0">
                        <button type="button" data-history-filter="todos" class="history-filter inline-flex h-11 shrink-0 items-center gap-2 rounded-md border border-blue-200 bg-blue-50 bg-[#1E3A8A] px-3 text-[10px] font-black uppercase tracking-widest text-blue-800 text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                            <i class="ph ph-list"></i>
                            Todos {{ $totalEmprestimos }}
                        </button>
                        <button type="button" data-history-filter="ativos" class="history-filter inline-flex h-11 shrink-0 items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-3 text-[10px] font-black uppercase tracking-widest text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                            <i class="ph ph-clock"></i>
                            Ativos {{ $ativos }}
                        </button>
                        <button type="button" data-history-filter="atrasados" class="history-filter inline-flex h-11 shrink-0 items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 text-[10px] font-black uppercase tracking-widest text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                            <i class="ph ph-warning-circle"></i>
                            Atrasados {{ $atrasados }}
                        </button>
                        <button type="button" data-history-filter="concluidos" class="history-filter inline-flex h-11 shrink-0 items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                            <i class="ph ph-check-circle"></i>
                            Concluídos {{ $concluidos }}
                        </button>
                        <button type="button" data-history-filter="reservas" class="history-filter inline-flex h-11 shrink-0 items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 text-[10px] font-black uppercase tracking-widest text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                            <i class="ph ph-bookmark-simple"></i>
                            Reservas {{ $reservasAtivas }}
                        </button>
                    </div>
                </div>
            </section>

            @if($reservas->isNotEmpty())
                <section data-history-section="reservas" class="history-reservation-section rounded-md border border-amber-200 bg-white/95 shadow-sm dark:border-amber-500/20 dark:bg-[#0d1420]/95">
                    <div class="grid grid-cols-1 gap-4 border-b border-slate-200 px-5 py-4 dark:border-white/10 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Fila de espera</p>
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Minhas reservas</h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Acompanhe posição, histórico e reservas já atendidas.</p>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 dark:border-amber-500/30 dark:bg-amber-500/10">
                                <p class="text-[9px] font-black uppercase tracking-widest text-amber-700 dark:text-amber-300">Ativas</p>
                                <p class="text-xl font-black text-amber-800 dark:text-amber-200">{{ $reservasAtivas }}</p>
                            </div>
                            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                                <p class="text-[9px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Atendidas</p>
                                <p class="text-xl font-black text-emerald-800 dark:text-emerald-200">{{ $reservasAtendidas }}</p>
                            </div>
                            <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 dark:border-white/10 dark:bg-white/[.03]">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Canceladas</p>
                                <p class="text-xl font-black text-slate-900 dark:text-white">{{ $reservasCanceladas }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-5 lg:grid-cols-2">
                        @foreach($reservas as $reserva)
                            @php
                                $reservaAtiva = $reserva->status === \App\Models\Reserva::STATUS_ATIVA;
                                $reservaStatusLabel = $reservaAtiva ? 'Reserva ativa' : ($reserva->status === \App\Models\Reserva::STATUS_CANCELADA ? 'Cancelada' : 'Atendida');
                                $esperaDias = $reserva->created_at->diffInDays(now());
                                $statusStyles = match ($reserva->status) {
                                    \App\Models\Reserva::STATUS_ATIVA => [
                                        'card' => 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10',
                                        'badge' => 'border-amber-200 bg-white text-amber-800 dark:border-amber-500/30 dark:bg-[#0d1420] dark:text-amber-300',
                                        'icon' => 'ph-hourglass-medium',
                                        'label' => 'Na fila',
                                    ],
                                    \App\Models\Reserva::STATUS_CANCELADA => [
                                        'card' => 'border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/[.03]',
                                        'badge' => 'border-slate-200 bg-white text-slate-600 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-300',
                                        'icon' => 'ph-x-circle',
                                        'label' => 'Cancelada',
                                    ],
                                    default => [
                                        'card' => 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10',
                                        'badge' => 'border-emerald-200 bg-white text-emerald-700 dark:border-emerald-500/30 dark:bg-[#0d1420] dark:text-emerald-300',
                                        'icon' => 'ph-check-circle',
                                        'label' => 'Atendida',
                                    ],
                                };
                            @endphp
                            <article class="history-card rounded-md border p-4 {{ $statusStyles['card'] }}" data-history-type="reservas" data-history-search="{{ Str::lower(($reserva->livro?->titulo ?? '') . ' ' . ($reserva->livro?->autor?->nome ?? '') . ' ' . $reservaStatusLabel) }}">
                                <div class="flex min-w-0 gap-4">
                                    <div class="h-24 w-16 shrink-0 overflow-hidden rounded-md bg-white ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10">
                                        @if($reserva->livro?->capa)
                                            <img src="{{ asset('storage/' . $reserva->livro->capa) }}" alt="{{ $reserva->livro?->titulo }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center">
                                                <i class="ph ph-bookmark-simple text-slate-400"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="line-clamp-2 text-sm font-black text-slate-950 dark:text-white">{{ $reserva->livro?->titulo ?? 'Livro removido' }}</h4>
                                            <span class="shrink-0 rounded-md border px-2 py-1 text-[9px] font-black uppercase tracking-widest {{ $statusStyles['badge'] }}">
                                                <i class="ph {{ $statusStyles['icon'] }}"></i>
                                                {{ $statusStyles['label'] }}
                                            </span>
                                        </div>
                                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $reserva->livro?->autor?->nome ?? 'Autor não informado' }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <div class="rounded-md border border-white/70 bg-white/70 p-3 dark:border-white/10 dark:bg-[#0d1420]/60">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Solicitada</p>
                                        <p class="mt-1 text-xs font-bold text-slate-900 dark:text-white">{{ $reserva->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="rounded-md border border-white/70 bg-white/70 p-3 dark:border-white/10 dark:bg-[#0d1420]/60">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Espera</p>
                                        <p class="mt-1 text-xs font-bold text-slate-900 dark:text-white">{{ $esperaDias }} dia{{ $esperaDias === 1 ? '' : 's' }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                                    @if($reservaAtiva)
                                        <span class="inline-flex h-9 items-center gap-2 rounded-md border border-amber-200 bg-white px-3 text-[10px] font-black uppercase tracking-widest text-amber-800 dark:border-amber-500/30 dark:bg-[#0d1420] dark:text-amber-300">
                                            <i class="ph ph-users-three"></i>
                                            Posição {{ $reserva->posicao_fila ?? '--' }} na fila
                                        </span>
                                        <form action="{{ route('reservas.cancelar', $reserva->id) }}" method="POST" data-confirm="delete" data-title="Cancelar reserva?" data-text="Você sairá da fila deste livro.">
                                            @csrf
                                            <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 text-[10px] font-black uppercase tracking-widest text-red-700 transition hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20">
                                                <i class="ph ph-x"></i>
                                                Cancelar
                                            </button>
                                        </form>
                                    @elseif($reserva->status === \App\Models\Reserva::STATUS_CANCELADA)
                                        <span class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-white px-3 text-[10px] font-black uppercase tracking-widest text-slate-600 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-300">Reserva cancelada</span>
                                    @else
                                        <span class="inline-flex h-9 items-center rounded-md border border-emerald-200 bg-white px-3 text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:border-emerald-500/30 dark:bg-[#0d1420] dark:text-emerald-300">Reserva atendida</span>
                                    @endif
                                    @if($reserva->livro)
                                        <a href="{{ route('livros.show', $reserva->livro->id) }}" class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-200 bg-white px-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                            <i class="ph ph-eye"></i>
                                            Ver livro
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="space-y-4">
                @forelse($emprestimos as $emp)
                    @php
                        $atrasado = $emp->isAtrasado();
                        $status = $emp->status;
                        $concluido = in_array($status, [\App\Models\Emprestimos::STATUS_DEVOLVIDO, \App\Models\Emprestimos::STATUS_ENCERRADO], true);
                        $ativo = in_array($status, \App\Models\Emprestimos::STATUS_EM_ANDAMENTO, true);
                        $multaPrevista = $atrasado ? \App\Models\Emprestimos::calcularMulta($emp->data_devolucao_prevista) : 0;
                        $statusLabel = match ($status) {
                            \App\Models\Emprestimos::STATUS_SOLICITADO => 'Solicitado',
                            \App\Models\Emprestimos::STATUS_APROVADO => 'Aprovado',
                            \App\Models\Emprestimos::STATUS_RETIRADO => 'Retirado',
                            \App\Models\Emprestimos::STATUS_EM_USO => 'Em uso',
                            \App\Models\Emprestimos::STATUS_DEVOLUCAO_SOLICITADA => 'Devolução solicitada',
                            \App\Models\Emprestimos::STATUS_DEVOLVIDO => 'Concluído',
                            \App\Models\Emprestimos::STATUS_ENCERRADO => 'Encerrado',
                            \App\Models\Emprestimos::STATUS_REJEITADO => 'Rejeitado',
                            default => 'Status indefinido',
                        };
                        $type = $atrasado ? 'atrasados' : ($concluido ? 'concluidos' : ($ativo ? 'ativos' : 'outros'));
                        $statusClasses = $atrasado
                            ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300'
                            : ($concluido
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
                                : 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300');
                        $diasRestantes = $emp->data_devolucao_prevista ? today()->diffInDays($emp->data_devolucao_prevista, false) : null;
                    @endphp

                    <article class="history-card rounded-md border bg-white/95 shadow-sm transition dark:bg-[#0d1420]/95 {{ $atrasado ? 'border-red-300 dark:border-red-500/30' : 'border-slate-200 dark:border-white/10' }}" data-history-type="{{ $type }}" data-history-search="{{ Str::lower(($emp->livro?->titulo ?? '') . ' ' . ($emp->livro?->autor?->nome ?? '') . ' ' . $statusLabel) }}">
                        <div class="grid gap-4 p-4 lg:grid-cols-[minmax(0,1fr)_220px] lg:items-center">
                            <div class="flex min-w-0 gap-4">
                                <div class="h-20 w-14 shrink-0 overflow-hidden rounded-md bg-slate-100 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10">
                                    @if($emp->livro?->capa)
                                        <img src="{{ asset('storage/' . $emp->livro->capa) }}" alt="{{ $emp->livro?->titulo }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <i class="ph ph-book text-xl text-slate-400"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-[10px] font-black uppercase tracking-widest {{ $statusClasses }}">
                                            {{ $atrasado ? 'Atrasado' : $statusLabel }}
                                        </span>
                                        @if((int) $emp->renovacoes_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                                                <i class="ph ph-arrows-clockwise"></i>
                                                {{ $emp->renovacoes_count }}x
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="mt-2 text-base font-black leading-tight text-slate-950 dark:text-white">{{ $emp->livro?->titulo ?? 'Livro removido' }}</h3>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $emp->livro?->autor?->nome ?? 'Autor não informado' }}</p>

                                    <div class="mt-3 grid gap-2 text-xs text-slate-600 dark:text-slate-400 sm:grid-cols-3">
                                        <p><span class="font-black uppercase tracking-wider text-slate-500">Retirada:</span> <strong class="text-slate-900 dark:text-white">{{ $emp->data_emprestimo ? $emp->data_emprestimo->format('d/m/Y') : '--' }}</strong></p>
                                        <p><span class="font-black uppercase tracking-wider text-slate-500">Prazo:</span> <strong class="{{ $atrasado ? 'text-red-700 dark:text-red-300' : 'text-slate-900 dark:text-white' }}">{{ $emp->data_devolucao_prevista ? $emp->data_devolucao_prevista->format('d/m/Y') : '--' }}</strong></p>
                                        <p><span class="font-black uppercase tracking-wider text-slate-500">Devolução:</span> <strong class="text-slate-900 dark:text-white">{{ $emp->data_devolucao_real ? $emp->data_devolucao_real->format('d/m/Y') : 'Pendente' }}</strong></p>
                                    </div>

                                    @if($diasRestantes !== null && ! $concluido)
                                        <p class="mt-3 text-xs font-semibold {{ $atrasado ? 'text-red-700 dark:text-red-300' : 'text-slate-500 dark:text-slate-400' }}">
                                            @if($atrasado)
                                                Prazo vencido há {{ abs($diasRestantes) }} dia{{ abs($diasRestantes) === 1 ? '' : 's' }}.
                                            @else
                                                Faltam {{ $diasRestantes }} dia{{ $diasRestantes === 1 ? '' : 's' }} para o prazo.
                                            @endif
                                        </p>
                                    @endif

                                    @if($emp->valor_multa > 0 || $multaPrevista > 0)
                                        <div class="mt-3 rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                                            <span class="font-black">Multa:</span>
                                            @if($emp->valor_multa > 0)
                                                R$ {{ number_format($emp->valor_multa, 2, ',', '.') }}
                                                {{ $emp->multaPendente() ? 'pendente de regularização' : 'regularizada' }}
                                            @else
                                                previsão atual de R$ {{ number_format($multaPrevista, 2, ',', '.') }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 lg:flex-col lg:items-stretch">
                                @if($emp->data_emprestimo && $emp->data_devolucao_prevista)
                                    <a href="{{ route('emprestimos.comprovante', $emp->id) }}" target="_blank" class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                        <i class="ph ph-file-pdf"></i>
                                        Comprovante
                                    </a>
                                @else
                                    <span class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-slate-200 bg-slate-100 px-3 text-[10px] font-black uppercase tracking-widest text-slate-400 dark:border-white/10 dark:bg-white/5 dark:text-slate-500">
                                        <i class="ph ph-clock"></i>
                                        Aguardando retirada
                                    </span>
                                @endif

                                @if(in_array($status, [\App\Models\Emprestimos::STATUS_RETIRADO, \App\Models\Emprestimos::STATUS_EM_USO], true))
                                    @if($emp->podeRenovar())
                                        <form action="{{ route('emprestimos.renovar', $emp->id) }}" method="POST" data-confirm="loan" data-title="Renovar empréstimo?" data-text="O prazo será recalculado conforme a regra deste livro.">
                                            @csrf
                                            <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                                <i class="ph ph-arrows-clockwise"></i>
                                                Renovar
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-slate-200 bg-slate-100 px-3 text-[10px] font-black uppercase tracking-widest text-slate-400 dark:border-white/10 dark:bg-white/5">
                                            <i class="ph ph-lock"></i>
                                            Renovação indisponível
                                        </span>
                                    @endif

                                    <form action="{{ route('emprestimos.solicitar-devolucao', $emp->id) }}" method="POST" data-confirm="return" data-title="Solicitar devolução?" data-text="A equipe da biblioteca será avisada para finalizar a devolução.">
                                        @csrf
                                        <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-3 text-[10px] font-black uppercase tracking-widest text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20">
                                            <i class="ph ph-arrow-u-up-left"></i>
                                            Solicitar devolução
                                        </button>
                                    </form>
                                @endif

                                @if($emp->multaPendente())
                                    <a href="{{ route('pagamentos.checkout', $emp) }}" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md bg-amber-500 px-3 text-[10px] font-black uppercase tracking-widest text-slate-950 transition hover:bg-amber-400">
                                        <i class="ph ph-credit-card"></i>
                                        Pagar multa
                                    </a>
                                @elseif((float) $emp->valor_multa > 0 && $emp->multa_paga_em)
                                    <span class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                                        <i class="ph ph-check-circle"></i>
                                        Multa paga
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-md border border-slate-200 bg-white/95 p-10 text-center shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                        <i class="ph ph-books mx-auto block text-5xl text-slate-300 dark:text-slate-600"></i>
                        <h3 class="mt-4 font-serif text-2xl font-black text-slate-950 dark:text-white">Nenhum empréstimo ainda</h3>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Explore o acervo e solicite seu primeiro livro.</p>
                        <a href="{{ route('dashboard') }}#acervo-section" class="mt-5 inline-flex h-11 items-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                            <i class="ph ph-books"></i>
                            Explorar acervo
                        </a>
                    </div>
                @endforelse

                <div id="history-empty-filter" class="hidden rounded-md border border-dashed border-slate-300 bg-white/80 p-8 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-[#0d1420]/80 dark:text-slate-400">
                    Nenhum item encontrado para esse filtro.
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('history-search');
            const filterButtons = Array.from(document.querySelectorAll('.history-filter'));
            const cards = Array.from(document.querySelectorAll('.history-card'));
            const empty = document.getElementById('history-empty-filter');
            const reservationSection = document.querySelector('.history-reservation-section');
            let activeFilter = 'todos';

            const normalize = (value) => (value || '').toString().toLowerCase();

            const applyFilters = () => {
                const query = normalize(searchInput?.value);
                let visible = 0;

                cards.forEach((card) => {
                    const type = card.dataset.historyType;
                    const text = normalize(card.dataset.historySearch);
                    const matchesType = activeFilter === 'todos' || type === activeFilter;
                    const matchesQuery = !query || text.includes(query);
                    const show = matchesType && matchesQuery;

                    card.classList.toggle('hidden', !show);

                    if (show) {
                        visible += 1;
                    }
                });

                if (reservationSection) {
                    const visibleReservations = Array.from(reservationSection.querySelectorAll('.history-card')).some((card) => !card.classList.contains('hidden'));
                    reservationSection.classList.toggle('hidden', activeFilter !== 'todos' && activeFilter !== 'reservas');
                    if ((activeFilter === 'todos' || activeFilter === 'reservas') && !visibleReservations && query) {
                        reservationSection.classList.add('hidden');
                    }
                }

                empty?.classList.toggle('hidden', visible > 0);
            };

            filterButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    activeFilter = button.dataset.historyFilter || 'todos';

                    filterButtons.forEach((item) => {
                        item.classList.remove('bg-[#1E3A8A]', 'text-white');
                    });

                    button.classList.add('bg-[#1E3A8A]', 'text-white');
                    applyFilters();
                });
            });

            searchInput?.addEventListener('input', applyFilters);
            applyFilters();
        });
    </script>
</x-app-layout>
