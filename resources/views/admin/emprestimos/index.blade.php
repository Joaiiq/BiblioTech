<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center gap-1 shrink-0">
                    <i class="ph ph-library text-[#1E3A8A] dark:text-blue-400 text-4xl"></i>
                    <div class="text-[11px] font-black tracking-tight text-center leading-tight">
                        <span class="text-[#1E3A8A] dark:text-blue-400">BIBLIO</span><br>
                        <span class="text-[#F59E0B]">TECH</span>
                    </div>
                </a>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[.15em] text-amber-500 mb-0.5">Administração</p>
                    <h1 class="text-lg font-black text-slate-900 dark:text-white">Painel de Empréstimos</h1>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="dark = !dark" class="w-9 h-9 rounded-md bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition">
                    <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
                </button>
                <x-user-avatar :user="auth()->guard('web')->user()" />
            </div>
        </div>
    </x-slot>

    <style>
        .bg-shelf { background: linear-gradient(90deg, transparent, rgba(30,58,138,.12) 20%, rgba(245,158,11,.22) 80%, transparent); }
        .dark .bg-shelf { background: linear-gradient(90deg, transparent, rgba(147,197,253,.07) 20%, rgba(147,197,253,.07) 80%, transparent); }
        .bg-icon { color: rgba(245,158,11,.16); pointer-events: none; user-select: none; }
        .dark .bg-icon { color: rgba(147,197,253,.07); }
        #bg-glow-1 { background: radial-gradient(circle, rgba(30,58,138,.14) 0%, transparent 70%); }
        #bg-glow-2 { background: radial-gradient(circle, rgba(245,158,11,.18) 0%, transparent 70%); }
        .dark #bg-glow-1 { background: radial-gradient(circle, rgba(30,58,138,.3) 0%, transparent 70%); }
        .dark #bg-glow-2 { background: radial-gradient(circle, rgba(245,158,11,.15) 0%, transparent 70%); }
    </style>

    <div class="-mx-4 px-4 py-10 bg-gradient-to-b from-slate-100 via-blue-50 to-slate-100 dark:from-[#0f172a] dark:via-[#0f172a] dark:to-[#0b1120] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 min-h-screen relative">

        {{-- ══ DECORATIVE BACKGROUND ══ --}}
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="bg-dots-emprestimos" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#93c5fd" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#bg-dots-emprestimos)"/>
            </svg>
            <div id="bg-glow-1" class="absolute -top-28 -left-20 w-96 h-96 rounded-full blur-[90px]"></div>
            <div id="bg-glow-2" class="absolute -bottom-20 -right-14 w-72 h-72 rounded-full blur-[80px]"></div>
            <div class="bg-shelf absolute left-0 right-0 h-px top-[22%]"></div>
            <div class="bg-shelf absolute left-0 right-0 h-px top-[58%]"></div>
            <i class="ph ph-book-open bg-icon absolute left-[3%] top-[5%] text-[28px]"></i>
            <i class="ph ph-hand-coins bg-icon absolute left-[87%] top-[8%] text-[22px]"></i>
            <i class="ph ph-calendar-blank bg-icon absolute left-[14%] top-[58%] text-[34px]"></i>
            <i class="ph ph-clipboard-text bg-icon absolute left-[74%] top-[54%] text-[26px]"></i>
        </div>

        <div class="max-w-7xl mx-auto relative z-10 space-y-8">

    {{-- DataTables CDN (sem CSS padrão — tema custom abaixo) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <style>
        /* DataTables precisa de CSS porque a lib injeta markup próprio. */
        #tabelaEmprestimos_wrapper {
            color: #475569;
            font-size: 0.8rem;
            font-family: 'Inter', sans-serif;
        }
        .dark #tabelaEmprestimos_wrapper { color: #94a3b8; }

        /* Controles: length + search */
        #tabelaEmprestimos_wrapper .dataTables_length,
        #tabelaEmprestimos_wrapper .dataTables_filter {
            display: flex;
            align-items: center;
        }
        #tabelaEmprestimos_wrapper .dataTables_length label,
        #tabelaEmprestimos_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            font-size: 0.72rem;
        }
        #tabelaEmprestimos_wrapper .dataTables_length select,
        #tabelaEmprestimos_wrapper .dataTables_filter input {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 0.72rem;
            outline: none;
            transition: border-color .15s;
        }
        .dark #tabelaEmprestimos_wrapper .dataTables_length select,
        .dark #tabelaEmprestimos_wrapper .dataTables_filter input {
            background: #0f172a;
            border-color: #1e293b;
            color: #e2e8f0;
        }
        #tabelaEmprestimos_wrapper .dataTables_filter input { min-width: 180px; }
        #tabelaEmprestimos_wrapper .dataTables_filter input:focus,
        #tabelaEmprestimos_wrapper .dataTables_length select:focus { border-color: #F59E0B; }

        /* Tabela */
        #tabelaEmprestimos_wrapper table.dataTable {
            width: 100% !important;
            border-collapse: collapse;
            margin: 0 !important;
        }
        #tabelaEmprestimos_wrapper table.dataTable thead th {
            background: #eaf2ff;
            color: #475569;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            padding: 10px 12px;
            border-top: none;
            border-bottom: 1px solid #cbd5e1;
            white-space: nowrap;
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 20px;
        }
        .dark #tabelaEmprestimos_wrapper table.dataTable thead th {
            background: transparent;
            border-bottom-color: #1e293b;
        }
        /* Ícone de sort */
        #tabelaEmprestimos_wrapper table.dataTable thead th.sorting,
        #tabelaEmprestimos_wrapper table.dataTable thead th.sorting_asc,
        #tabelaEmprestimos_wrapper table.dataTable thead th.sorting_desc {
            background-image: none !important;
        }
        #tabelaEmprestimos_wrapper table.dataTable thead th.sorting::after    { content: '↕'; position:absolute; right:5px; color:#334155; font-size:.6rem; top:50%; transform:translateY(-50%); }
        #tabelaEmprestimos_wrapper table.dataTable thead th.sorting_asc::after  { content: '↑'; position:absolute; right:5px; color:#F59E0B; font-size:.6rem; top:50%; transform:translateY(-50%); }
        #tabelaEmprestimos_wrapper table.dataTable thead th.sorting_desc::after { content: '↓'; position:absolute; right:5px; color:#F59E0B; font-size:.6rem; top:50%; transform:translateY(-50%); }
        #tabelaEmprestimos_wrapper table.dataTable thead th:before { display:none; }
        #tabelaEmprestimos_wrapper table.dataTable thead th.no-sort::after { content: ''; }

        #tabelaEmprestimos_wrapper table.dataTable tbody td {
            padding: 11px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .dark #tabelaEmprestimos_wrapper table.dataTable tbody td { border-bottom-color: #1e293b; }
        #tabelaEmprestimos_wrapper table.dataTable tbody tr:last-child td { border-bottom: none; }
        #tabelaEmprestimos_wrapper table.dataTable tbody tr:hover td { background: rgba(30,58,138,.05); }
        #tabelaEmprestimos_wrapper table.dataTable tbody tr:nth-child(even) td { background: rgba(15,23,42,.025); }
        .dark #tabelaEmprestimos_wrapper table.dataTable tbody tr:hover td { background: rgba(255,255,255,.025); }
        .dark #tabelaEmprestimos_wrapper table.dataTable tbody tr:nth-child(even) td { background: rgba(255,255,255,.012); }
        #tabelaEmprestimos_wrapper table.dataTable.no-footer { border-bottom: none; }
        #tabelaEmprestimos_wrapper .dataTables_scroll {
            overflow: hidden;
        }
        #tabelaEmprestimos_wrapper .dataTables_scrollBody {
            scrollbar-color: #F59E0B #dbe3ef;
            scrollbar-width: thin;
        }
        .dark #tabelaEmprestimos_wrapper .dataTables_scrollBody {
            scrollbar-color: #F59E0B #0f172a;
        }
        #tabelaEmprestimos_wrapper .dataTables_scrollBody::-webkit-scrollbar {
            height: 10px;
        }
        #tabelaEmprestimos_wrapper .dataTables_scrollBody::-webkit-scrollbar-track {
            background: #dbe3ef;
            border-radius: 999px;
        }
        #tabelaEmprestimos_wrapper .dataTables_scrollBody::-webkit-scrollbar-thumb {
            background: #F59E0B;
            border: 2px solid #dbe3ef;
            border-radius: 999px;
        }
        .dark #tabelaEmprestimos_wrapper .dataTables_scrollBody::-webkit-scrollbar-track {
            background: #0f172a;
        }
        .dark #tabelaEmprestimos_wrapper .dataTables_scrollBody::-webkit-scrollbar-thumb {
            border-color: #0f172a;
        }

        /* Rodapé: info + paginação */
        #tabelaEmprestimos_wrapper .dataTables_info {
            font-size: 0.7rem;
            color: #475569;
            padding: 0;
        }
        #tabelaEmprestimos_wrapper .dataTables_paginate { padding: 0; }
        #tabelaEmprestimos_wrapper .dataTables_paginate .paginate_button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 26px;
            padding: 0 7px;
            margin: 0 2px;
            border: 1px solid #cbd5e1 !important;
            border-radius: 5px;
            font-size: 0.7rem;
            color: #64748b !important;
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
            background: #ffffff !important;
        }
        .dark #tabelaEmprestimos_wrapper .dataTables_paginate .paginate_button {
            border-color: #1e293b !important;
            background: transparent !important;
        }
        #tabelaEmprestimos_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background: #e2e8f0 !important;
            border-color: #F59E0B !important;
            color: #F59E0B !important;
        }
        .dark #tabelaEmprestimos_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background: #1e293b !important;
        }
        #tabelaEmprestimos_wrapper .dataTables_paginate .paginate_button.current,
        #tabelaEmprestimos_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #1E3A8A !important;
            border-color: #1E3A8A !important;
            color: #fff !important;
        }
        #tabelaEmprestimos_wrapper .dataTables_paginate .paginate_button.disabled { opacity: .3; cursor: not-allowed; }
        [data-loans-scroll][disabled] { opacity: .35; cursor: not-allowed; }

        /* Card ativo */
        .card-filtro.ativo { outline: 2px solid; outline-offset: -2px; }
        .card-filtro.ativo.c-all  { outline-color: #3b82f6; }
        .card-filtro.ativo.c-ok   { outline-color: #10b981; }
        .card-filtro.ativo.c-late { outline-color: #ef4444; }
        .card-filtro.ativo.c-done { outline-color: #10b981; }
    </style>

    @php
        $solicitados = $emprestimos->where('status', \App\Models\Emprestimos::STATUS_SOLICITADO)->count();
        $aprovados   = $emprestimos->where('status', \App\Models\Emprestimos::STATUS_APROVADO)->count();
        $emUso       = $emprestimos->whereIn('status', [\App\Models\Emprestimos::STATUS_RETIRADO, \App\Models\Emprestimos::STATUS_EM_USO, \App\Models\Emprestimos::STATUS_DEVOLUCAO_SOLICITADA])->count();
        $atrasados   = $emprestimos->filter(fn($e) => $e->isAtrasado())->count();
        $concluidos  = $emprestimos->whereIn('status', [\App\Models\Emprestimos::STATUS_DEVOLVIDO, \App\Models\Emprestimos::STATUS_ENCERRADO])->count();
        $rejeitados  = $emprestimos->where('status', \App\Models\Emprestimos::STATUS_REJEITADO)->count();
        $multas      = $emprestimos->filter(fn($e) => $e->multaPendente())->sum('valor_multa');
        $totalReservas = ($reservasAtivas ?? collect())->count();
    @endphp

    <div class="max-w-7xl mx-auto space-y-5">
        <div class="bg-white/95 dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-xl px-5 py-4 grid grid-cols-1 md:grid-cols-5 gap-3 shadow-sm">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-slate-500">Livro comum</p>
                <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ \App\Models\Emprestimos::PRAZO_LIVRO_COMUM_DIAS }} dias</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-slate-500">Destaque</p>
                <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ \App\Models\Emprestimos::PRAZO_BESTSELLER_DIAS }} dias</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-slate-500">Multa</p>
                <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">R$ {{ number_format(\App\Models\Emprestimos::VALOR_MULTA_DIARIA, 2, ',', '.') }}/dia</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-slate-500">Lembrete</p>
                <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ \App\Models\Emprestimos::DIAS_ANTECEDENCIA_LEMBRETE }} dias antes</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-slate-500">Renovação</p>
                <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ \App\Models\Emprestimos::MAX_RENOVACOES }} vez</p>
            </div>
        </div>

        {{-- ── Cards compactos clicáveis ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">

            <button onclick="filtrarCard(this, 'todos', 'Todos os registros')"
                    class="card-filtro c-all ativo bg-white/95 dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-xl px-4 py-3
                           flex items-center gap-3 hover:border-blue-600/60 transition-all text-left w-full shadow-sm">
                <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                    <i class="ph ph-stack text-slate-500 dark:text-slate-400 text-base"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Total</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white leading-tight font-serif">{{ $emprestimos->count() }}</p>
                </div>
            </button>

                <button onclick="filtrarCard(this, 'solicitado', 'Solicitações')"
                    class="card-filtro c-ok bg-white/95 dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-xl px-4 py-3
                           flex items-center gap-3 hover:border-blue-700/60 transition-all text-left w-full shadow-sm">
                <span class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                    <i class="ph ph-handshake text-blue-600 dark:text-blue-400 text-base"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Solicitados</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white leading-tight font-serif">{{ $solicitados }}</p>
                </div>
            </button>

            <button onclick="filtrarCard(this, 'aprovado', 'Aprovados')"
                    class="card-filtro c-ok bg-white/95 dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-xl px-4 py-3
                           flex items-center gap-3 hover:border-indigo-700/60 transition-all text-left w-full shadow-sm">
                <span class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                    <i class="ph ph-check text-indigo-600 dark:text-indigo-300 text-base"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Aprovados</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white leading-tight font-serif">{{ $aprovados }}</p>
                </div>
            </button>

            <button onclick="filtrarCard(this, 'em_andamento', 'Em uso')"
                    class="card-filtro c-late bg-white/95 dark:bg-[#111827] border {{ $atrasados > 0 ? 'border-red-300 dark:border-red-900/50' : 'border-slate-200 dark:border-[#1e293b]' }} rounded-xl px-4 py-3
                           flex items-center gap-3 hover:border-red-700/60 transition-all text-left w-full shadow-sm">
                <span class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                    <i class="ph ph-book-open text-red-600 dark:text-red-400 text-base"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Em uso</p>
                    <p class="text-xl font-black {{ $emUso > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }} leading-tight font-serif">{{ $emUso }}</p>
                </div>
            </button>

            <button onclick="filtrarCard(this, 'atrasado', 'Empréstimos atrasados')"
                    class="card-filtro c-done bg-white/95 dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-xl px-4 py-3
                           flex items-center gap-3 hover:border-emerald-700/60 transition-all text-left w-full shadow-sm">
                <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                    <i class="ph ph-warning-circle text-emerald-600 dark:text-emerald-400 text-base"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Atrasados</p>
                    <p class="text-xl font-black {{ $atrasados > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }} leading-tight font-serif">{{ $atrasados }}</p>
                </div>
            </button>

            <button onclick="filtrarCard(this, 'concluido', 'Empréstimos concluídos')"
                    class="card-filtro c-done bg-white/95 dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-xl px-4 py-3
                           flex items-center gap-3 hover:border-emerald-700/60 transition-all text-left w-full shadow-sm">
                <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                    <i class="ph ph-check-circle text-emerald-600 dark:text-emerald-400 text-base"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Concluídos</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white leading-tight font-serif">{{ $concluidos }}</p>
                </div>
            </button>

            <button onclick="filtrarCard(this, 'rejeitado', 'Solicitações rejeitadas')"
                    class="card-filtro c-late bg-white/95 dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-xl px-4 py-3
                           flex items-center gap-3 hover:border-rose-700/60 transition-all text-left w-full shadow-sm">
                <span class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center shrink-0">
                    <i class="ph ph-x-circle text-rose-600 dark:text-rose-400 text-base"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Rejeitados</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white leading-tight font-serif">{{ $rejeitados }}</p>
                </div>
            </button>

        </div>

        @if($totalReservas > 0)
            <div class="bg-white/95 dark:bg-[#111827] border border-amber-300/70 dark:border-amber-500/30 rounded-xl overflow-hidden shadow-sm">
                <div class="px-5 py-4 border-b border-amber-200 dark:border-amber-500/20 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center">
                            <i class="ph ph-bookmark-simple text-amber-700 dark:text-amber-300"></i>
                        </span>
                        <div>
                            <p class="text-xs font-black uppercase tracking-[.16em] text-amber-700 dark:text-amber-300">Reservas em fila</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Membros aguardando livros sem estoque.</p>
                        </div>
                    </div>
                    <span class="text-sm font-black text-slate-900 dark:text-white">{{ $totalReservas }}</span>
                </div>

                <div class="divide-y divide-slate-200 dark:divide-[#1e293b]">
                    @foreach($reservasAtivas->groupBy('livro_id') as $reservasDoLivro)
                        @php
                            $primeira = $reservasDoLivro->first();
                            $estoqueDisponivel = (int) ($primeira->livro->quantidade ?? 0);
                        @endphp
                        <div class="px-5 py-4 grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $primeira->livro->titulo ?? 'Livro removido' }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $primeira->livro?->autor?->nome ?? '—' }} · {{ $reservasDoLivro->count() }} reserva{{ $reservasDoLivro->count() === 1 ? '' : 's' }} · {{ $estoqueDisponivel }} disponível
                                </p>
                            </div>
                            <div class="flex flex-col lg:items-end gap-3">
                                <div class="flex flex-wrap gap-2 lg:justify-end">
                                    @foreach($reservasDoLivro->take(4)->values() as $index => $reserva)
                                        <span class="inline-flex items-center gap-2 rounded-lg border {{ $index === 0 ? 'border-amber-300 bg-amber-50 text-amber-900 dark:border-amber-500/50 dark:bg-amber-500/10 dark:text-amber-100' : 'border-slate-200 bg-slate-50 text-slate-700 dark:border-[#1e293b] dark:bg-[#0f172a] dark:text-slate-300' }} px-3 py-2 text-xs">
                                            <strong class="{{ $index === 0 ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500' }}">#{{ $index + 1 }}</strong>
                                            {{ $reserva->membro->nome ?? 'Membro removido' }}
                                        </span>
                                    @endforeach
                                    @if($reservasDoLivro->count() > 4)
                                        <span class="inline-flex items-center rounded-lg border border-slate-200 dark:border-[#1e293b] px-3 py-2 text-xs text-slate-500">
                                            +{{ $reservasDoLivro->count() - 4 }}
                                        </span>
                                    @endif
                                </div>
                                @if($estoqueDisponivel > 0)
                                    <form action="{{ route('admin.reservas.atender', $primeira->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-lg bg-amber-700 text-white border border-amber-600 hover:bg-amber-600 transition-all">
                                            <i class="ph ph-check"></i> Atender #1
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                        <i class="ph ph-clock"></i> aguardando devolução
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Painel da tabela ── --}}
        <div class="bg-white/95 dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-xl overflow-hidden shadow-sm">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-[#1e293b]">
                <div class="flex items-center gap-2">
                    <i class="ph ph-funnel text-[#F59E0B] text-sm"></i>
                    <span id="tituloFiltro" class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        Todos os registros
                    </span>
                </div>
                @if($multas > 0)
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 rounded-lg px-3 py-1">
                    <i class="ph ph-coins"></i>
                    R$&nbsp;{{ number_format($multas, 2, ',', '.') }} em multas pendentes
                </span>
                @endif
            </div>

            <div class="px-5 pt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="flex items-center gap-2 text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                    <i class="ph ph-arrows-left-right text-[#F59E0B]"></i>
                    Em telas pequenas, use as setas ou arraste a tabela.
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" data-loans-scroll="-1" class="grid h-8 w-8 place-items-center rounded-md border border-slate-200 bg-slate-100 text-slate-600 transition hover:border-[#F59E0B]/60 hover:text-[#F59E0B] dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10" aria-label="Rolar tabela para esquerda">
                        <i class="ph ph-caret-left"></i>
                    </button>
                    <button type="button" data-loans-scroll="1" class="grid h-8 w-8 place-items-center rounded-md border border-slate-200 bg-slate-100 text-slate-600 transition hover:border-[#F59E0B]/60 hover:text-[#F59E0B] dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10" aria-label="Rolar tabela para direita">
                        <i class="ph ph-caret-right"></i>
                    </button>
                </div>
            </div>

            <div class="p-5 pt-3">
                <table id="tabelaEmprestimos" data-has-rows="{{ $emprestimos->isNotEmpty() ? '1' : '0' }}" class="min-w-[980px]" style="width:100%">
                    <thead>
                        <tr>
                            <th>Membro</th>
                            <th>Livro</th>
                            <th>Prazo</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emprestimos as $emprestimo)
                            @php
                                $atrasado = $emprestimo->isAtrasado();
                                $status = $emprestimo->status;
                                $pagamentoPendente = $emprestimo->pagamentos?->firstWhere('status', \App\Models\Pagamento::STATUS_PENDENTE);
                                $pagamentoAprovado = $emprestimo->pagamentoAprovado;
                                $filtrosLinha = [$status];
                                if (in_array($status, \App\Models\Emprestimos::STATUS_EM_ANDAMENTO, true)) {
                                    $filtrosLinha[] = 'em_andamento';
                                }
                                if (in_array($status, [\App\Models\Emprestimos::STATUS_DEVOLVIDO, \App\Models\Emprestimos::STATUS_ENCERRADO], true)) {
                                    $filtrosLinha[] = 'concluido';
                                }
                                if ($atrasado) {
                                    $filtrosLinha[] = 'atrasado';
                                }
                                $prazoAutomatico = \App\Models\Emprestimos::prazoDiasParaLivro($emprestimo->livro);
                                $multaPrevista = $atrasado
                                    ? \App\Models\Emprestimos::calcularMulta($emprestimo->data_devolucao_prevista)
                                    : 0;
                                $statusLabel = match ($status) {
                                    \App\Models\Emprestimos::STATUS_SOLICITADO => 'Solicitado',
                                    \App\Models\Emprestimos::STATUS_APROVADO => 'Aprovado',
                                    \App\Models\Emprestimos::STATUS_RETIRADO => 'Retirado',
                                    \App\Models\Emprestimos::STATUS_EM_USO => 'Em uso',
                                    \App\Models\Emprestimos::STATUS_DEVOLUCAO_SOLICITADA => 'Devolução solicitada',
                                    \App\Models\Emprestimos::STATUS_DEVOLVIDO => 'Concluído',
                                    \App\Models\Emprestimos::STATUS_ENCERRADO => 'Encerrado',
                                    default => '—',
                                };
                            @endphp
                            <tr data-status-filter="{{ implode(' ', array_unique($filtrosLinha)) }}">
                                <td>
                                    @if($emprestimo->membro && $emprestimo->membro->name)
                                        <div class="flex items-center gap-2">
                                            <x-user-avatar :user="$emprestimo->membro" size="h-7 w-7" text="text-[10px]" />
                                            <span class="text-slate-800 dark:text-slate-200 font-medium text-sm">{{ $emprestimo->membro->name }}</span>
                                        </div>
                                    @elseif($emprestimo->membro)
                                        <span class="text-red-400 text-xs">ID {{ $emprestimo->membro->user_id }} sem nome</span>
                                    @else
                                        <span class="text-orange-400 text-xs">Membro #{{ $emprestimo->membro_id }} não encontrado</span>
                                    @endif
                                </td>

                                <td>
                                    @if($emprestimo->livro)
                                        <div class="space-y-1">
                                            <span class="block text-slate-700 dark:text-slate-400 italic text-sm">{{ $emprestimo->livro->titulo }}</span>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest {{ $emprestimo->livro->e_bestseller ? 'text-amber-700 dark:text-amber-300' : 'text-blue-700 dark:text-blue-300' }}">
                                                <i class="ph {{ $emprestimo->livro->e_bestseller ? 'ph-star' : 'ph-book' }}"></i>
                                                Prazo {{ $prazoAutomatico }}d
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-orange-400 text-xs not-italic">Livro #{{ $emprestimo->livro_id }} não encontrado</span>
                                    @endif
                                </td>

                                <td class="text-center tabular-nums text-slate-600 dark:text-slate-400 text-xs">
                                    <div class="space-y-1">
                                        <p>{{ $emprestimo->data_devolucao_prevista ? $emprestimo->data_devolucao_prevista->format('d/m/Y') : '—' }}</p>
                                        @if((int) $emprestimo->renovacoes_count > 0)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">
                                                <i class="ph ph-arrows-clockwise"></i>
                                                {{ $emprestimo->renovacoes_count }} renovação
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="text-center">
                                    @if($atrasado)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-900/40 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/50">
                                            <i class="ph ph-clock"></i> Atrasado
                                        </span>
                                        @if($multaPrevista > 0)
                                            <p class="mt-1 text-[10px] font-bold text-red-700 dark:text-red-300">
                                                Multa prevista: R$ {{ number_format($multaPrevista, 2, ',', '.') }}
                                            </p>
                                        @endif
                                    @elseif($status === \App\Models\Emprestimos::STATUS_SOLICITADO)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50">
                                            <i class="ph ph-handshake"></i> Solicitado
                                        </span>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_APROVADO)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/50">
                                            <i class="ph ph-check"></i> Aprovado
                                        </span>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_RETIRADO)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">
                                            <i class="ph ph-bag"></i> Retirado
                                        </span>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_EM_USO)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50">
                                            <i class="ph ph-book-open"></i> Em uso
                                        </span>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_DEVOLUCAO_SOLICITADA)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">
                                            <i class="ph ph-arrow-u-up-left"></i> Devolução solicitada
                                        </span>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_DEVOLVIDO)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                            <i class="ph ph-check"></i> Concluído
                                        </span>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_REJEITADO)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">
                                            <i class="ph ph-x-circle"></i> Rejeitado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-900/60 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800/50">
                                            <i class="ph ph-archive"></i> Encerrado
                                        </span>
                                    @endif
                                    @if($status === \App\Models\Emprestimos::STATUS_REJEITADO && $emprestimo->rejected_reason)
                                        <p class="mt-1 text-[10px] text-rose-700 dark:text-rose-300">{{ $emprestimo->rejected_reason }}</p>
                                    @endif
                                    @if((float) $emprestimo->valor_multa > 0)
                                        @if($emprestimo->multaPendente())
                                            <p class="mt-1 text-[10px] font-bold text-red-700 dark:text-red-300">
                                                Multa pendente: R$ {{ number_format($emprestimo->valor_multa, 2, ',', '.') }}
                                            </p>
                                            @if($pagamentoPendente)
                                                <p class="mt-1 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                                    Pagamento {{ $pagamentoPendente->codigo }} em análise
                                                </p>
                                            @elseif($pagamentoAprovado)
                                                <p class="mt-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-300">
                                                    Pagamento aprovado. Falta sincronizar.
                                                </p>
                                            @else
                                                <p class="mt-1 text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                                                    Aguardando pagamento do membro.
                                                </p>
                                            @endif
                                        @else
                                            <p class="mt-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-300">
                                                Multa regularizada em {{ $emprestimo->multa_paga_em?->format('d/m/Y') }}
                                            </p>
                                        @endif
                                    @endif
                                </td>

                                <td class="text-right">
                                    @if($status === \App\Models\Emprestimos::STATUS_SOLICITADO)
                                        <div class="inline-flex items-center gap-2">
                                            <form action="{{ route('admin.emprestimos.aprovar', $emprestimo->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-lg bg-[#1E3A8A] text-white border border-blue-800/80 hover:bg-blue-700 hover:border-blue-500 transition-all">
                                                    <i class="ph ph-check"></i> Aprovar
                                                </button>
                                            </form>
                                            <details class="group">
                                                <summary class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-lg bg-rose-50 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50 cursor-pointer">
                                                    <i class="ph ph-x"></i> Rejeitar
                                                </summary>
                                                <form action="{{ route('admin.emprestimos.rejeitar', $emprestimo->id) }}" method="POST" class="mt-2">
                                                    @csrf
                                                    <input name="motivo" placeholder="Motivo (opcional)" class="w-52 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-[#1e293b] text-slate-800 dark:text-slate-200 rounded-md px-2 py-1 text-[11px]" />
                                                    <button type="submit" class="ml-2 text-[11px] uppercase tracking-wider text-rose-700 hover:text-rose-800 dark:text-rose-300 dark:hover:text-rose-200">Confirmar</button>
                                                </form>
                                            </details>
                                        </div>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_APROVADO)
                                        <form action="{{ route('admin.emprestimos.retirar', $emprestimo->id) }}" method="POST" class="inline-flex items-center gap-2">
                                            @csrf
                                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                                {{ $prazoAutomatico }}d automático
                                            </span>
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-lg bg-amber-700 text-white border border-amber-600 hover:bg-amber-600 transition-all">
                                                <i class="ph ph-bag"></i> Retirada
                                            </button>
                                        </form>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_RETIRADO)
                                        <div class="inline-flex items-center gap-2">
                                            <form action="{{ route('admin.emprestimos.iniciar-uso', $emprestimo->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-lg bg-blue-700 text-white border border-blue-600 hover:bg-blue-600 transition-all">
                                                    <i class="ph ph-book-open"></i> Em uso
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.emprestimos.devolver', $emprestimo->id) }}" method="POST">
                                                @csrf
                                                <button type="button" onclick="confirmarDevolucao(event, this.closest('form'))" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-lg bg-[#1E3A8A] text-white border border-blue-800/80 hover:bg-blue-700 hover:border-blue-500 transition-all">
                                                    <i class="ph ph-arrow-u-up-left"></i> Receber
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_EM_USO)
                                        <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold flex items-center justify-end gap-1">
                                            <i class="ph ph-clock"></i> Aguardando devolução
                                        </span>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_DEVOLUCAO_SOLICITADA)
                                        <form action="{{ route('admin.emprestimos.devolver', $emprestimo->id) }}" method="POST">
                                            @csrf
                                            <button type="button" onclick="confirmarDevolucao(event, this.closest('form'))" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-lg bg-[#1E3A8A] text-white border border-blue-800/80 hover:bg-blue-700 hover:border-blue-500 transition-all">
                                                <i class="ph ph-arrow-u-up-left"></i> Receber
                                            </button>
                                        </form>
                                    @elseif($status === \App\Models\Emprestimos::STATUS_DEVOLVIDO)
                                        <div class="inline-flex items-center gap-2">
                                            @if($emprestimo->multaPendente())
                                                @if($pagamentoAprovado)
                                                    <form action="{{ route('admin.emprestimos.regularizar-multa', $emprestimo->id) }}" method="POST">
                                                        @csrf
                                                        <button
                                                            type="button"
                                                            onclick="confirmarRegularizacao(event, this.closest('form'), true)"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-lg bg-emerald-700 text-white border border-emerald-600 hover:bg-emerald-600 transition-all">
                                                            <i class="ph ph-check-circle"></i> Confirmar multa
                                                        </button>
                                                    </form>
                                                @else
                                                    <button
                                                        type="button"
                                                        onclick="confirmarRegularizacao(event, null, false)"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-lg bg-slate-100 text-slate-500 border border-slate-200 transition hover:border-amber-300 hover:text-amber-700 dark:bg-white/5 dark:text-slate-400 dark:border-white/10">
                                                        <i class="ph ph-lock-key"></i> Multa pendente
                                                    </button>
                                                @endif
                                            @else
                                                <form action="{{ route('admin.emprestimos.encerrar', $emprestimo->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider rounded-lg bg-emerald-700 text-white border border-emerald-600 hover:bg-emerald-600 transition-all">
                                                        <i class="ph ph-archive"></i> Encerrar
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold flex items-center justify-end gap-1">
                                            <i class="ph ph-archive"></i> Encerrado
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-16">
                                    <i class="ph ph-tray text-slate-600 text-4xl block mb-2"></i>
                                    <p class="text-slate-500 text-sm">Nenhum empréstimo registrado.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let tabela;
        let filtroStatusAtual = 'todos';

        $(document).ready(function () {
            const hasRows = document.getElementById('tabelaEmprestimos')?.dataset.hasRows === '1';
            if (!hasRows) {
                return;
            }

            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'tabelaEmprestimos' || filtroStatusAtual === 'todos') {
                    return true;
                }

                const row = settings.aoData[dataIndex]?.nTr;
                const filtros = row?.dataset.statusFilter?.split(' ') || [];

                return filtros.includes(filtroStatusAtual);
            });

            tabela = $('#tabelaEmprestimos').DataTable({
                language: {
                    search: "",
                    searchPlaceholder: "Buscar...",
                    lengthMenu:   "Mostrar _MENU_ linhas",
                    info:         "_START_–_END_ de _TOTAL_",
                    infoEmpty:    "0 registros",
                    infoFiltered: "(de _MAX_)",
                    paginate:     { next: "→", previous: "←" },
                    emptyTable:   "Nenhum registro encontrado",
                },
                columnDefs: [
                    { orderable: false, targets: [4] },
                    { className: 'no-sort', targets: [4] },
                    { className: 'dt-center', targets: [2, 3] },
                    { className: 'dt-right',  targets: [4] },
                ],
                order:      [[2, 'asc']],
                pageLength: 15,
                scrollX: true,
                // DOM: controles em cima (flex), tabela, rodapé (flex)
                dom: '<"flex items-center justify-between mb-3 gap-3 flex-wrap"lf>t<"flex items-center justify-between mt-4 pt-3 border-t border-slate-200 dark:border-[#1e293b] gap-3 flex-wrap"ip>',
            });

            document.querySelectorAll('[data-loans-scroll]').forEach((button) => {
                button.addEventListener('click', () => {
                    const body = document.querySelector('#tabelaEmprestimos_wrapper .dataTables_scrollBody');
                    if (!body) return;

                    body.scrollBy({
                        left: Number(button.dataset.loansScroll) * Math.max(260, body.clientWidth * 0.7),
                        behavior: 'smooth',
                    });
                });
            });

            const updateScrollButtons = () => {
                const body = document.querySelector('#tabelaEmprestimos_wrapper .dataTables_scrollBody');
                if (!body) return;

                document.querySelectorAll('[data-loans-scroll]').forEach((button) => {
                    const direction = Number(button.dataset.loansScroll);
                    const atStart = body.scrollLeft <= 2;
                    const atEnd = body.scrollLeft + body.clientWidth >= body.scrollWidth - 2;
                    button.disabled = direction < 0 ? atStart : atEnd;
                });
            };

            const body = document.querySelector('#tabelaEmprestimos_wrapper .dataTables_scrollBody');
            body?.addEventListener('scroll', updateScrollButtons, { passive: true });
            window.addEventListener('resize', updateScrollButtons);
            tabela.on('draw', updateScrollButtons);
            updateScrollButtons();
        });

        function filtrarCard(btn, status, titulo) {
            document.querySelectorAll('.card-filtro').forEach(c => c.classList.remove('ativo'));
            btn.classList.add('ativo');
            document.getElementById('tituloFiltro').textContent = titulo;
            if (!tabela) return;
            filtroStatusAtual = status;
            tabela.draw();
        }

        function confirmarDevolucao(event, form) {
            event.preventDefault();
            darkSwal.fire({
                title: 'Registrar Devolução?',
                text: 'Confirme o recebimento do livro.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'CONFIRMAR',
                cancelButtonText:  'CANCELAR',
            }).then(r => { if (r.isConfirmed) form.submit(); });
        }

        function confirmarRegularizacao(event, form, pagamentoAprovado) {
            event.preventDefault();

            if (!pagamentoAprovado) {
                darkSwal.fire({
                    title: 'Multa não paga',
                    text: 'Não existe pagamento aprovado para esta multa. O membro precisa enviar o pagamento e a equipe deve aprovar antes da regularização.',
                    icon: 'warning',
                    confirmButtonText: 'ENTENDI',
                });
                return;
            }

            darkSwal.fire({
                title: 'Pagamento aprovado',
                text: 'Esta multa possui pagamento aprovado. Deseja confirmar a regularização no empréstimo?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'REGULARIZAR',
                cancelButtonText: 'CANCELAR',
            }).then(r => { if (r.isConfirmed && form) form.submit(); });
        }
    </script>

        </div>
    </div>

</x-app-layout>
