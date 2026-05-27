<x-app-layout>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

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
                    <h1 class="text-lg font-black text-slate-900 dark:text-white">Painel Membros</h1>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('membros.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#1E3A8A] text-white text-[11px] font-black uppercase tracking-widest hover:bg-blue-700 transition">
                    <i class="ph ph-user-plus text-sm"></i>
                    Novo Membro
                </a>
                <button type="button" @click="dark = !dark" class="w-9 h-9 rounded-md bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition">
                    <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        .members-page-bg {
            background:
                radial-gradient(circle at top left, rgba(30,58,138,.10), transparent 32rem),
                radial-gradient(circle at bottom right, rgba(245,158,11,.16), transparent 28rem),
                #eaf0f8;
        }
        .dark .members-page-bg {
            background:
                radial-gradient(circle at top left, rgba(30,58,138,.20), transparent 32rem),
                radial-gradient(circle at bottom right, rgba(245,158,11,.10), transparent 28rem),
                #0f172a;
        }
        .members-panel {
            background: rgba(248,250,252,.92);
            border-color: rgba(148,163,184,.45);
            box-shadow: 0 18px 45px rgba(15,23,42,.08);
        }
        .dark .members-panel {
            background: #111827;
            border-color: #1e293b;
            box-shadow: none;
        }
        .bg-shelf { background: linear-gradient(90deg, transparent, rgba(30,58,138,.18) 20%, rgba(245,158,11,.24) 80%, transparent); }
        .dark .bg-shelf { background: linear-gradient(90deg, transparent, rgba(147,197,253,.07) 20%, rgba(245,158,11,.10) 80%, transparent); }
        .bg-icon { color: rgba(30,58,138,.13); pointer-events: none; user-select: none; }
        .book-icon { color: rgba(245,158,11,.38); }
        .dark .bg-icon { color: rgba(147,197,253,.07); }
        .dark .book-icon { color: rgba(245,158,11,.20); }
        #bg-glow-members-1 { background: radial-gradient(circle, rgba(30,58,138,.20) 0%, transparent 70%); }
        #bg-glow-members-2 { background: radial-gradient(circle, rgba(245,158,11,.26) 0%, transparent 70%); }
        .dark #bg-glow-members-1 { background: radial-gradient(circle, rgba(30,58,138,.28) 0%, transparent 70%); }
        .dark #bg-glow-members-2 { background: radial-gradient(circle, rgba(245,158,11,.14) 0%, transparent 70%); }

        #membrosTable_wrapper { color: #94a3b8; font-size: .8rem; font-family: Inter, sans-serif; }
        #membrosTable_wrapper .dataTables_length,
        #membrosTable_wrapper .dataTables_filter { display: flex; align-items: center; padding: 0 1.25rem 1rem; }
        #membrosTable_wrapper .dataTables_length label,
        #membrosTable_wrapper .dataTables_filter label { display: flex; align-items: center; gap: .5rem; color: #64748b; font-size: .72rem; }
        #membrosTable_wrapper .dataTables_filter { justify-content: flex-end; }
        #membrosTable_wrapper .dataTables_filter input,
        #membrosTable_wrapper .dataTables_length select {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            border-radius: 6px;
            padding: 7px 10px;
            outline: none;
        }
        .dark #membrosTable_wrapper .dataTables_filter input,
        .dark #membrosTable_wrapper .dataTables_length select {
            background: #0f172a;
            border-color: #1e293b;
            color: #e2e8f0;
        }
        #membrosTable_wrapper table.dataTable { width: 100% !important; border-collapse: collapse; margin: 0 !important; }
        #membrosTable_wrapper table.dataTable thead th {
            text-align: left !important;
            color: #64748b;
            font-size: .68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 12px 16px;
            border-bottom: 1px solid #cbd5e1;
            background: #dbeafe;
            white-space: nowrap;
        }
        .dark #membrosTable_wrapper table.dataTable thead th { background: #0d1420; }
        #membrosTable_wrapper table.dataTable tbody td {
            text-align: left !important;
            padding: 14px 16px;
            border-bottom: 1px solid #dbe3ef;
            vertical-align: middle;
        }
        .dark #membrosTable_wrapper table.dataTable tbody td { border-bottom-color: #1e293b; }
        #membrosTable_wrapper table.dataTable tbody tr:hover td { background: rgba(30,58,138,.055); }
        .dark #membrosTable_wrapper table.dataTable tbody tr:hover td { background: rgba(255,255,255,.025); }
        #membrosTable_wrapper .dataTables_info { color: #64748b; font-size: .72rem; padding: 1rem 1.25rem; }
        #membrosTable_wrapper .dataTables_paginate { padding: 1rem 1.25rem; }
        #membrosTable_wrapper .dataTables_paginate .paginate_button {
            border: 1px solid #cbd5e1 !important;
            border-radius: 5px;
            color: #334155 !important;
            background: transparent !important;
            margin: 0 2px;
            padding: 4px 9px;
            font-size: .72rem;
        }
        #membrosTable_wrapper .dataTables_paginate .paginate_button.current {
            background: #1E3A8A !important;
            border-color: #1E3A8A !important;
            color: white !important;
        }
        .dark #membrosTable_wrapper .dataTables_paginate .paginate_button {
            border-color: #1e293b !important;
            color: #94a3b8 !important;
        }
        .table-scroll-wrap {
            overflow-x: auto;
            padding-bottom: .5rem;
            overscroll-behavior-x: contain;
            scrollbar-width: thin;
            scrollbar-color: #F59E0B #dbe3ef;
        }
        .dark .table-scroll-wrap { scrollbar-color: #F59E0B #0f172a; }
        .table-scroll-wrap::-webkit-scrollbar { height: 11px; }
        .table-scroll-wrap::-webkit-scrollbar-track { background: #dbe3ef; border-radius: 999px; }
        .table-scroll-wrap::-webkit-scrollbar-thumb { background: #F59E0B; border-radius: 999px; border: 2px solid #dbe3ef; }
        .dark .table-scroll-wrap::-webkit-scrollbar-track { background: #0f172a; }
        .dark .table-scroll-wrap::-webkit-scrollbar-thumb { border-color: #0f172a; }
        @media (max-width: 768px) {
            #membrosTable_wrapper .dataTables_length,
            #membrosTable_wrapper .dataTables_filter {
                padding: 0 .75rem .75rem;
                justify-content: flex-start;
            }
            #membrosTable_wrapper .dataTables_filter input { max-width: 220px; }
        }
        .status-filter.active { outline: 2px solid; outline-offset: -2px; }
        .status-filter[data-filter="todos"].active { outline-color: #3b82f6; }
        .status-filter[data-filter="bom"].active { outline-color: #10b981; }
        .status-filter[data-filter="devendo"].active { outline-color: #f59e0b; }
        .status-filter[data-filter="com_multa"].active { outline-color: #ef4444; }
        .situacao-badge-bom { background: #dcfce7; color: #047857; border-color: #86efac; }
        .situacao-badge-devendo { background: #fef3c7; color: #b45309; border-color: #fcd34d; }
        .situacao-badge-multa { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }
        .dark .situacao-badge-bom { background: rgba(6,78,59,.35); color: #a7f3d0; border-color: rgba(16,185,129,.45); }
        .dark .situacao-badge-devendo { background: rgba(120,53,15,.35); color: #fcd34d; border-color: rgba(245,158,11,.45); }
        .dark .situacao-badge-multa { background: rgba(127,29,29,.35); color: #fecaca; border-color: rgba(239,68,68,.45); }
    </style>

    <div class="-mx-4 px-4 py-8 members-page-bg sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 min-h-screen relative">
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="bg-dots-members" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#93c5fd" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#bg-dots-members)"/>
            </svg>
            <div id="bg-glow-members-1" class="absolute -top-28 -left-20 w-96 h-96 rounded-full blur-[90px]"></div>
            <div id="bg-glow-members-2" class="absolute -bottom-20 -right-14 w-72 h-72 rounded-full blur-[80px]"></div>
            <div class="bg-shelf absolute left-0 right-0 h-px top-[22%]"></div>
            <div class="bg-shelf absolute left-0 right-0 h-px top-[58%]"></div>
            <i class="ph ph-users-three bg-icon absolute left-[3%] top-[5%] text-[28px]"></i>
            <i class="ph ph-identification-card bg-icon absolute left-[74%] top-[54%] text-[26px]"></i>
            <i class="ph ph-book-open bg-icon book-icon absolute left-[14%] top-[58%] text-[34px]"></i>
        </div>

        <div class="max-w-7xl mx-auto relative z-10 space-y-5">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <button type="button" data-filter="todos" class="status-filter active members-panel border rounded-md px-4 py-3 flex items-center justify-between text-left">
                    <span>
                        <span class="block text-[10px] uppercase tracking-widest text-slate-500">Total</span>
                        <span class="block text-xl font-black text-slate-900 dark:text-white">{{ $totalMembros }}</span>
                    </span>
                    <i class="ph ph-users text-blue-500 text-lg"></i>
                </button>
                <button type="button" data-filter="bom" class="status-filter members-panel border rounded-md px-4 py-3 flex items-center justify-between text-left">
                    <span>
                        <span class="block text-[10px] uppercase tracking-widest text-slate-500">Em dia</span>
                        <span class="block text-xl font-black text-emerald-600 dark:text-emerald-400">{{ count($membrosBom) }}</span>
                    </span>
                    <i class="ph ph-check-circle text-emerald-500 text-lg"></i>
                </button>
                <button type="button" data-filter="devendo" class="status-filter members-panel border rounded-md px-4 py-3 flex items-center justify-between text-left">
                    <span>
                        <span class="block text-[10px] uppercase tracking-widest text-slate-500">Devendo</span>
                        <span class="block text-xl font-black text-amber-500">{{ count($membrosDevendo) }}</span>
                    </span>
                    <i class="ph ph-warning-circle text-amber-500 text-lg"></i>
                </button>
                <button type="button" data-filter="com_multa" class="status-filter members-panel border rounded-md px-4 py-3 flex items-center justify-between text-left">
                    <span>
                        <span class="block text-[10px] uppercase tracking-widest text-slate-500">Com multa</span>
                        <span class="block text-xl font-black text-red-500">{{ count($membrosComMulta) }}</span>
                    </span>
                    <i class="ph ph-coins text-red-500 text-lg"></i>
                </button>
            </div>

            <div class="members-panel border rounded-md overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-[#1e293b] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <i class="ph ph-table text-[#F59E0B] text-sm"></i>
                            <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest">Lista de membros</h2>
                            <span id="filtroAtual" class="text-[10px] text-slate-400 bg-slate-100 dark:bg-white/5 px-2 py-0.5 rounded-md font-bold">Todos</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-gray-500">Busca por nome, email, CPF, telefone, carteirinha, tipo e situação.</p>
                    </div>
                    <button type="button" id="limparFiltros" class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-md bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-gray-300 text-[11px] font-bold uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-white/10 transition">
                        <i class="ph ph-x text-sm"></i>
                        Limpar filtros
                    </button>
                </div>

                <div class="px-5 pt-3 flex items-center justify-between gap-3">
                    <div class="text-[11px] font-semibold text-slate-500 dark:text-gray-500 flex items-center gap-2">
                        <i class="ph ph-arrows-left-right text-[#F59E0B]"></i>
                        Use as setas ou arraste a tabela.
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="scrollTableLeft" class="w-8 h-8 shrink-0 rounded-md flex items-center justify-center bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-[#F59E0B] hover:border-[#F59E0B]/60 transition" aria-label="Rolar tabela para esquerda">
                            <i class="ph ph-caret-left"></i>
                        </button>
                        <button type="button" id="scrollTableRight" class="w-8 h-8 shrink-0 rounded-md flex items-center justify-center bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-[#F59E0B] hover:border-[#F59E0B]/60 transition" aria-label="Rolar tabela para direita">
                            <i class="ph ph-caret-right"></i>
                        </button>
                    </div>
                </div>

                <div id="membrosTableScroll" class="pt-4 table-scroll-wrap">
                    <table id="membrosTable" class="display">
                        <thead>
                            <tr>
                                <th>Membro</th>
                                <th>Identificação</th>
                                <th>Contato</th>
                                <th>Empréstimos</th>
                                <th>Situação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allMembros as $item)
                                @php
                                    $membro = $item['membro'];
                                    $perfil = $item['perfil'];
                                    $perfilLabel = match ($perfil) {
                                        'com_multa' => 'Com multa',
                                        'devendo' => 'Devendo',
                                        default => 'Em dia',
                                    };
                                    $perfilClasses = match ($perfil) {
                                        'com_multa' => 'situacao-badge-multa',
                                        'devendo' => 'situacao-badge-devendo',
                                        default => 'situacao-badge-bom',
                                    };
                                    $ultimoEmprestimo = $item['ultimoEmprestimo'];
                                @endphp
                                <tr data-perfil="{{ $perfil }}">
                                    <td data-search="{{ $membro->nome }} {{ $membro->email }} {{ $perfilLabel }}">
                                        <div class="flex items-center gap-3 min-w-[230px]">
                                            <x-user-avatar :user="$membro" size="h-10 w-10" text="text-xs" />
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $membro->nome }}</p>
                                                <p class="text-[11px] text-slate-500 dark:text-gray-500 truncate">{{ $membro->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-search="{{ $membro->numero_carteirinha }} {{ $membro->cpf }} {{ $membro->tipo_membro }}">
                                        <div class="space-y-1 min-w-[170px]">
                                            <p class="text-xs text-blue-600 dark:text-blue-400 font-mono font-bold">{{ $membro->numero_carteirinha ?? 'Sem carteirinha' }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-gray-500">CPF: {{ $membro->cpf ?? 'Não informado' }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-gray-500">Tipo: {{ $membro->tipo_membro ?? 'Não informado' }}</p>
                                        </div>
                                    </td>
                                    <td data-search="{{ $membro->telefone }} {{ $membro->endereco }}">
                                        <div class="space-y-1 min-w-[190px]">
                                            <p class="text-xs text-slate-900 dark:text-white">{{ $membro->telefone ?? 'Sem telefone' }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-gray-500 truncate max-w-[220px]">{{ $membro->endereco ?? 'Sem endereço' }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-gray-500">Cadastro: {{ $membro->created_at?->format('d/m/Y') ?? '—' }}</p>
                                        </div>
                                    </td>
                                    <td data-search="{{ $item['emprestimosAtivos'] }} ativos {{ $item['emprestimosCompletados'] }} completos {{ count($item['emprestimosAtrasados']) }} atrasados">
                                        <div class="grid grid-cols-3 gap-2 min-w-[210px]">
                                            <div>
                                                <p class="text-[10px] uppercase tracking-widest text-slate-500">Ativos</p>
                                                <p class="text-sm font-black text-blue-500">{{ $item['emprestimosAtivos'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] uppercase tracking-widest text-slate-500">Atrasos</p>
                                                <p class="text-sm font-black {{ count($item['emprestimosAtrasados']) ? 'text-amber-400' : 'text-slate-400' }}">{{ count($item['emprestimosAtrasados']) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] uppercase tracking-widest text-slate-500">Total</p>
                                                <p class="text-sm font-black text-slate-900 dark:text-white">{{ $item['totalEmprestimos'] }}</p>
                                            </div>
                                        </div>
                                        @if($ultimoEmprestimo?->created_at)
                                            <p class="mt-2 text-[11px] text-slate-500 dark:text-gray-500">Último: {{ $ultimoEmprestimo->created_at->format('d/m/Y') }}</p>
                                        @endif
                                    </td>
                                    <td data-search="{{ $perfilLabel }} multa {{ $item['multasNaoPagas'] }}">
                                        <div class="space-y-2 min-w-[150px]">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $perfilClasses }}">
                                                @if($perfil === 'com_multa')
                                                    <i class="ph ph-coins"></i>
                                                @elseif($perfil === 'devendo')
                                                    <i class="ph ph-warning-circle"></i>
                                                @else
                                                    <i class="ph ph-check-circle"></i>
                                                @endif
                                                {{ $perfilLabel }}
                                            </span>
                                            <p class="text-xs {{ $item['multasNaoPagas'] > 0 ? 'text-red-400 font-bold' : 'text-slate-500 dark:text-gray-500' }}">
                                                Multa: R$ {{ number_format($item['multasNaoPagas'], 2, ',', '.') }}
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-2 min-w-[190px]">
                                            <a href="{{ route('admin.membros.show', $membro->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#1E3A8A]/10 border border-[#1E3A8A]/30 text-blue-700 dark:text-blue-400 hover:bg-[#1E3A8A]/20 text-[11px] font-bold uppercase tracking-widest transition" aria-label="Ver perfil de {{ $membro->nome }}">
                                                <i class="ph ph-eye text-sm"></i>
                                                Ver
                                            </a>
                                            <a href="{{ route('membros.edit', $membro->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-300 hover:bg-amber-500/20 text-[11px] font-bold uppercase tracking-widest transition" aria-label="Editar {{ $membro->nome }}">
                                                <i class="ph ph-pencil-simple text-sm"></i>
                                                Editar
                                            </a>
                                            <button type="button" class="sendMsgBtn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-gray-300 hover:bg-slate-200 dark:hover:bg-white/10 text-[11px] font-bold uppercase tracking-widest transition" data-id="{{ $membro->id }}" data-nome="{{ e($membro->nome) }}" aria-label="Enviar mensagem para {{ $membro->nome }}">
                                                <i class="ph ph-envelope-simple text-sm"></i>
                                                Mensagem
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="messageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-lg mx-4">
            <div class="bg-white dark:bg-[#0d1420] rounded-md p-6 border border-slate-200 dark:border-white/10 shadow-2xl">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <i class="ph ph-envelope-simple text-blue-500 text-lg"></i>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Enviar Mensagem</h4>
                    </div>
                    <button type="button" id="cancelMsg" class="w-8 h-8 rounded-md bg-slate-100 dark:bg-white/5 text-slate-500 hover:text-slate-900 dark:hover:text-white transition flex items-center justify-center">
                        <i class="ph ph-x text-sm"></i>
                    </button>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-gray-500 mb-4 uppercase tracking-wider">Para: <strong id="modalMemberName" class="text-slate-900 dark:text-white normal-case tracking-normal text-sm"></strong></p>
                <form id="messageForm" class="space-y-4">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-1 block">Assunto</label>
                        <input type="text" name="subject" id="msgSubject" class="w-full px-3 py-2 rounded-md border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 transition" required>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-1 block">Mensagem</label>
                        <textarea name="message" id="msgBody" rows="5" class="w-full px-3 py-2 rounded-md border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 transition resize-none" required></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" id="cancelMsgBtn2" class="px-4 py-2 rounded-md bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-gray-300 text-[11px] font-bold uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-white/10 transition">Cancelar</button>
                        <button type="submit" class="px-5 py-2 rounded-md bg-[#1E3A8A] border border-blue-800 text-white text-[11px] font-black uppercase tracking-widest hover:bg-blue-700 transition">
                            <i class="ph ph-paper-plane-right mr-1"></i> Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        let filtroPerfil = 'todos';
        const filtroAtual = document.getElementById('filtroAtual');
        const filtros = document.querySelectorAll('.status-filter');

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'membrosTable') return true;
            if (filtroPerfil === 'todos') return true;
            const row = settings.aoData[dataIndex].nTr;
            return row?.dataset.perfil === filtroPerfil;
        });

        const tabela = $('#membrosTable').DataTable({
            pageLength: 10,
            order: [[0, 'asc']],
            language: {
                search: '',
                searchPlaceholder: 'Buscar por nome, CPF, email, telefone...',
                lengthMenu: 'Mostrar _MENU_',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ membros',
                infoEmpty: 'Nenhum membro encontrado',
                zeroRecords: 'Nenhum membro encontrado',
                paginate: { previous: 'Anterior', next: 'Próximo' },
            },
            columnDefs: [
                { orderable: false, targets: 5 },
            ],
        });

        filtros.forEach(btn => {
            btn.addEventListener('click', () => {
                filtros.forEach(item => item.classList.remove('active'));
                btn.classList.add('active');
                filtroPerfil = btn.dataset.filter;
                filtroAtual.textContent = btn.querySelector('span span')?.textContent || 'Todos';
                tabela.draw();
            });
        });

        document.getElementById('limparFiltros').addEventListener('click', () => {
            filtroPerfil = 'todos';
            filtros.forEach(item => item.classList.toggle('active', item.dataset.filter === 'todos'));
            filtroAtual.textContent = 'Todos';
            tabela.search('').draw();
            $('#membrosTable_filter input').val('');
        });

        const tableScroll = document.getElementById('membrosTableScroll');
        document.getElementById('scrollTableLeft')?.addEventListener('click', () => {
            tableScroll?.scrollBy({ left: -360, behavior: 'smooth' });
        });
        document.getElementById('scrollTableRight')?.addEventListener('click', () => {
            tableScroll?.scrollBy({ left: 360, behavior: 'smooth' });
        });

        const messageModal = document.getElementById('messageModal');
        const modalMemberName = document.getElementById('modalMemberName');
        const messageForm = document.getElementById('messageForm');
        let currentMemberId = null;

        function openModal(id, name) {
            currentMemberId = id;
            modalMemberName.textContent = name;
            document.getElementById('msgSubject').value = '';
            document.getElementById('msgBody').value = '';
            messageModal.classList.remove('hidden');
            messageModal.classList.add('flex');
        }

        function closeModal() {
            currentMemberId = null;
            messageModal.classList.remove('flex');
            messageModal.classList.add('hidden');
        }

        document.addEventListener('click', event => {
            const btn = event.target.closest('.sendMsgBtn');
            if (btn) openModal(btn.dataset.id, btn.dataset.nome);
        });

        document.getElementById('cancelMsg').addEventListener('click', closeModal);
        document.getElementById('cancelMsgBtn2').addEventListener('click', closeModal);
        messageModal.addEventListener('click', event => { if (event.target === messageModal) closeModal(); });

        messageForm.addEventListener('submit', async event => {
            event.preventDefault();
            if (!currentMemberId) return;

            const formData = new FormData(messageForm);
            const response = await fetch(`/admin/membros/${currentMemberId}/message`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    subject: formData.get('subject'),
                    message: formData.get('message'),
                }),
            });

            if (response.ok) {
                closeModal();
                if (typeof Swal !== 'undefined') {
                    Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, background: '#0d1420', color: '#fff' })
                        .fire({ icon: 'success', title: 'Mensagem enviada' });
                }
            }
        });
    });
    </script>
</x-app-layout>
