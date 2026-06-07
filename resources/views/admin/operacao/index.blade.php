<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 flex-col items-center justify-center gap-1">
                    <i class="ph ph-library text-4xl text-[#1E3A8A] dark:text-blue-400"></i>
                    <div class="text-center text-[11px] font-black leading-tight tracking-tight">
                        <span class="text-[#1E3A8A] dark:text-blue-400">BIBLIO</span><br>
                        <span class="text-[#F59E0B]">TECH</span>
                    </div>
                </a>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Administração</p>
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Painel Operacional</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Filas do balcão, devoluções, reservas e pendências</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.emprestimos.index') }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-list-checks"></i>
                    Empréstimos
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
                    <pattern id="operacao-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#operacao-dots)"/>
            </svg>
            <i class="ph ph-books absolute left-[8%] top-[16%] text-[46px] text-amber-500/25 dark:text-amber-300/10"></i>
            <i class="ph ph-clock-countdown absolute right-[12%] top-[24%] text-[42px] text-blue-800/10 dark:text-blue-300/10"></i>
            <i class="ph ph-handshake absolute right-[22%] bottom-[16%] text-[48px] text-emerald-500/15 dark:text-emerald-300/10"></i>
        </div>

        <main class="relative z-10 mx-auto max-w-7xl space-y-6">
            <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95 sm:p-6">
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Atendimento presencial</p>
                        <h2 class="mt-1 font-serif text-2xl font-black text-slate-950 dark:text-white">Empréstimo de balcão</h2>
                        <p class="mt-2 max-w-2xl text-sm text-slate-500 dark:text-slate-400">Use este fluxo quando o bibliotecário estiver entregando o exemplar diretamente ao membro na biblioteca. O sistema já registra retirada imediata e prazo correto.</p>
                    </div>
                    <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Regra operacional</p>
                        <ul class="mt-3 space-y-2 text-sm">
                            <li>Livro comum: 14 dias.</li>
                            <li>Bestseller: 7 dias.</li>
                            <li>Pedido aprovado ou reserva atendida: retirada em até {{ \App\Models\Emprestimos::PRAZO_RETIRADA_DIAS }} dias.</li>
                        </ul>
                    </div>
                </div>

                <form action="{{ route('admin.emprestimos.balcao') }}" method="POST" class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]" data-confirm="loan" data-title="Registrar empréstimo presencial?" data-text="O exemplar será baixado do estoque e o prazo será aplicado imediatamente.">
                    @csrf
                    <div>
                        <label for="balcao_membro_id" class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Membro <span class="text-red-500">*</span></label>
                        <select id="balcao_membro_id" name="membro_id" required class="block h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-white">
                            <option value="">Selecione o membro</option>
                            @foreach($membrosBalcao as $membro)
                                <option value="{{ $membro->id }}">{{ $membro->nome }}{{ $membro->numero_carteirinha ? ' · ' . $membro->numero_carteirinha : '' }}{{ $membro->cpf ? ' · CPF ' . $membro->cpf : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="balcao_livro_id" class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Livro <span class="text-red-500">*</span></label>
                        <select id="balcao_livro_id" name="livro_id" required class="block h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-white">
                            <option value="">Selecione o exemplar</option>
                            @foreach($livrosBalcao as $livro)
                                <option value="{{ $livro->id }}">{{ $livro->titulo }} · {{ $livro->autor?->nome ?? 'Autor não informado' }} · {{ $livro->quantidade }} disp.</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-5 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                            <i class="ph ph-handshake"></i>
                            Registrar no balcão
                        </button>
                    </div>
                </form>
            </section>

            <section class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
                <div class="rounded-md border border-blue-200 bg-white/95 p-4 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">Solicitações</p>
                        <i class="ph ph-inbox text-xl text-blue-600 dark:text-blue-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $metricas['solicitacoes'] }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">aguardando aprovação</p>
                </div>

                <div class="rounded-md border border-indigo-200 bg-white/95 p-4 shadow-sm dark:border-indigo-500/30 dark:bg-indigo-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-700 dark:text-indigo-300">Retirada</p>
                        <i class="ph ph-bag text-xl text-indigo-600 dark:text-indigo-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $metricas['aprovados'] }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">aprovados no balcão</p>
                </div>

                <div class="rounded-md border border-emerald-200 bg-white/95 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Reservas</p>
                        <i class="ph ph-bookmark-simple text-xl text-emerald-600 dark:text-emerald-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $metricas['reservas_atendiveis'] }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">com exemplar disponível</p>
                </div>

                <div class="rounded-md border border-amber-200 bg-white/95 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-amber-700 dark:text-amber-300">Hoje</p>
                        <i class="ph ph-calendar-check text-xl text-amber-600 dark:text-amber-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $metricas['vencendo_hoje'] }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">vencem hoje</p>
                </div>

                <div class="rounded-md border border-red-200 bg-white/95 p-4 shadow-sm dark:border-red-500/30 dark:bg-red-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-red-700 dark:text-red-300">Atrasos</p>
                        <i class="ph ph-warning-circle text-xl text-red-600 dark:text-red-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $metricas['atrasados'] }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">fora do prazo</p>
                </div>

                <div class="rounded-md border border-orange-200 bg-white/95 p-4 shadow-sm dark:border-orange-500/30 dark:bg-orange-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-orange-700 dark:text-orange-300">Multas</p>
                        <i class="ph ph-currency-circle-dollar text-xl text-orange-600 dark:text-orange-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">R$ {{ number_format($metricas['total_multas'], 2, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $metricas['multas_pendentes'] }} pendente{{ $metricas['multas_pendentes'] === 1 ? '' : 's' }}</p>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Receber</p>
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Devoluções solicitadas</h2>
                    </div>
                    <div class="divide-y divide-slate-200 dark:divide-white/10">
                        @forelse($devolucoesSolicitadas as $emprestimo)
                            <article class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black text-slate-950 dark:text-white">{{ $emprestimo->livro?->titulo ?? 'Livro removido' }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $emprestimo->membro?->nome ?? 'Membro removido' }}</p>
                                    </div>
                                    <span class="rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">balcão</span>
                                </div>
                                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Prazo {{ $emprestimo->data_devolucao_prevista?->format('d/m/Y') ?? '--' }}</p>
                                <form action="{{ route('admin.emprestimos.devolver', $emprestimo->id) }}" method="POST" class="mt-4" data-confirm="loan" data-title="Finalizar devolução?" data-text="A multa será calculada automaticamente se houver atraso.">
                                    @csrf
                                    <button class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                        <i class="ph ph-arrow-u-up-left"></i>
                                        Finalizar devolução
                                    </button>
                                </form>
                            </article>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Nenhuma devolução solicitada.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-red-700 dark:text-red-300">Prioridade</p>
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Empréstimos atrasados</h2>
                    </div>
                    <div class="divide-y divide-slate-200 dark:divide-white/10">
                        @forelse($atrasados as $emprestimo)
                            @php
                                $dias = $emprestimo->data_devolucao_prevista ? (int) $emprestimo->data_devolucao_prevista->diffInDays(today()) : 0;
                                $multaPrevista = \App\Models\Emprestimos::calcularMulta($emprestimo->data_devolucao_prevista);
                            @endphp
                            <article class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black text-slate-950 dark:text-white">{{ $emprestimo->livro?->titulo ?? 'Livro removido' }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $emprestimo->membro?->nome ?? 'Membro removido' }}</p>
                                    </div>
                                    <span class="rounded-md border border-red-200 bg-red-50 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">{{ $dias }}d</span>
                                </div>
                                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Prevista para {{ $emprestimo->data_devolucao_prevista?->format('d/m/Y') ?? '--' }} · multa parcial R$ {{ number_format($multaPrevista, 2, ',', '.') }}</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if($emprestimo->membro)
                                        <a href="{{ route('admin.membros.show', $emprestimo->membro_id) }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                            <i class="ph ph-user"></i>
                                            Perfil
                                        </a>
                                    @endif
                                    @if($emprestimo->status === \App\Models\Emprestimos::STATUS_DEVOLUCAO_SOLICITADA)
                                        <form action="{{ route('admin.emprestimos.devolver', $emprestimo->id) }}" method="POST" data-confirm="loan" data-title="Receber livro atrasado?" data-text="O sistema vai registrar a devolução e calcular a multa.">
                                            @csrf
                                            <button class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                                <i class="ph ph-arrow-u-up-left"></i>
                                                Receber
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex h-9 items-center rounded-md border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-400">Aguardando membro</span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Sem atrasos no momento.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Fila</p>
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Reservas atendíveis</h2>
                    </div>
                    <div class="divide-y divide-slate-200 dark:divide-white/10">
                        @forelse($reservasAtendiveis as $reserva)
                            <article class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black text-slate-950 dark:text-white">{{ $reserva->livro?->titulo ?? 'Livro removido' }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $reserva->membro?->nome ?? 'Membro removido' }}</p>
                                    </div>
                                    <span class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $reserva->livro?->quantidade ?? 0 }} disp.</span>
                                </div>
                                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Reservado em {{ $reserva->created_at?->format('d/m/Y H:i') ?? '--' }}</p>
                                <form action="{{ route('admin.reservas.atender', $reserva->id) }}" method="POST" class="mt-4" data-confirm="loan" data-title="Atender reserva?" data-text="Será criado um empréstimo aprovado para o primeiro membro da fila.">
                                    @csrf
                                    <button class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-emerald-700">
                                        <i class="ph ph-check-circle"></i>
                                        Atender reserva
                                    </button>
                                </form>
                            </article>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Nenhuma reserva pronta para atendimento.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                <div class="overflow-hidden rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Balcão</p>
                            <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Fila de empréstimos</h2>
                        </div>
                        <a href="{{ route('admin.emprestimos.index') }}" class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                            <i class="ph ph-arrow-square-out"></i>
                            Ver tudo
                        </a>
                    </div>

                    <div class="overflow-x-auto overscroll-x-contain pb-2">
                        <table class="min-w-[940px] w-full text-left">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:bg-[#080d14] dark:text-slate-400">
                                <tr>
                                    <th class="px-5 py-3">Membro</th>
                                    <th class="px-5 py-3">Livro</th>
                                    <th class="px-5 py-3">Situação</th>
                                    <th class="px-5 py-3 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-white/10">
                                @forelse($solicitacoes->concat($aprovados) as $emprestimo)
                                    <tr class="align-top">
                                        <td class="px-5 py-4">
                                            <p class="font-bold text-slate-950 dark:text-white">{{ $emprestimo->membro?->nome ?? 'Membro removido' }}</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $emprestimo->membro?->email ?? 'Sem email' }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="font-bold text-slate-950 dark:text-white">{{ $emprestimo->livro?->titulo ?? 'Livro removido' }}</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $emprestimo->livro?->autor?->nome ?? 'Autor não informado' }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            @if($emprestimo->status === \App\Models\Emprestimos::STATUS_SOLICITADO)
                                                <span class="inline-flex rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">Solicitado</span>
                                            @else
                                                <span class="inline-flex rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300">Aprovado</span>
                                            @endif
                                            @if($emprestimo->status === \App\Models\Emprestimos::STATUS_APROVADO && $emprestimo->data_limite_retirada)
                                                <p class="mt-2 text-xs font-bold text-amber-700 dark:text-amber-300">Retirar até {{ $emprestimo->data_limite_retirada->format('d/m/Y') }}</p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex justify-end gap-2">
                                                @if($emprestimo->status === \App\Models\Emprestimos::STATUS_SOLICITADO)
                                                    <form action="{{ route('admin.emprestimos.aprovar', $emprestimo->id) }}" method="POST" data-confirm="loan" data-title="Aprovar solicitação?" data-text="O exemplar será reservado para retirada.">
                                                        @csrf
                                                        <button class="inline-flex h-9 items-center gap-2 rounded-md bg-[#1E3A8A] px-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                                            <i class="ph ph-check"></i>
                                                            Aprovar
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.emprestimos.rejeitar', $emprestimo->id) }}" method="POST" data-confirm="delete" data-title="Rejeitar solicitação?" data-text="O membro será avisado que a solicitação foi rejeitada.">
                                                        @csrf
                                                        <button class="inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 text-[10px] font-black uppercase tracking-widest text-red-700 transition hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20">
                                                            <i class="ph ph-x"></i>
                                                            Rejeitar
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.emprestimos.retirar', $emprestimo->id) }}" method="POST" data-confirm="loan" data-title="Confirmar retirada?" data-text="O prazo será aplicado automaticamente conforme a regra do livro.">
                                                        @csrf
                                                        <button class="inline-flex h-9 items-center gap-2 rounded-md bg-amber-600 px-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-amber-700">
                                                            <i class="ph ph-bag"></i>
                                                            Retirar
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Nenhum empréstimo aguardando ação.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-orange-700 dark:text-orange-300">Financeiro</p>
                            <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Multas pendentes</h2>
                        </div>
                        <a href="{{ route('admin.multas.index') }}" class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                            <i class="ph ph-arrow-square-out"></i>
                            Abrir
                        </a>
                    </div>
                    <div class="divide-y divide-slate-200 dark:divide-white/10">
                        @forelse($multasPendentes as $multa)
                            <article class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black text-slate-950 dark:text-white">{{ $multa->membro?->nome ?? 'Membro removido' }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $multa->livro?->titulo ?? 'Livro removido' }}</p>
                                    </div>
                                    <span class="text-sm font-black text-red-700 dark:text-red-300">R$ {{ number_format($multa->valor_multa, 2, ',', '.') }}</span>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if($multa->membro)
                                        <a href="{{ route('admin.membros.show', $multa->membro_id) }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                            <i class="ph ph-user"></i>
                                            Perfil
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.emprestimos.regularizar-multa', $multa->id) }}" method="POST" data-confirm="loan" data-title="Regularizar multa?" data-text="A multa será marcada como paga.">
                                        @csrf
                                        <button class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-emerald-700">
                                            <i class="ph ph-check-circle"></i>
                                            Regularizar
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Nenhuma multa pendente.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            @if($vencendoHoje->isNotEmpty())
                <section class="rounded-md border border-amber-200 bg-amber-50/90 p-5 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Lembrete do dia</p>
                            <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Empréstimos que vencem hoje</h2>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($vencendoHoje as $emprestimo)
                                <span class="rounded-md border border-amber-200 bg-white px-3 py-2 text-xs font-bold text-amber-800 shadow-sm dark:border-amber-500/30 dark:bg-[#0d1420] dark:text-amber-200">
                                    {{ $emprestimo->membro?->nome ?? 'Membro' }} · {{ $emprestimo->livro?->titulo ?? 'Livro' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </main>
    </div>
</x-app-layout>
