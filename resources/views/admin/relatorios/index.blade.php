<x-app-layout>
    @php
        $relatorios = [
            'visao_geral' => ['label' => 'Visão geral', 'icon' => 'ph-gauge'],
            'acervo' => ['label' => 'Acervo', 'icon' => 'ph-books'],
            'circulacao' => ['label' => 'Circulação', 'icon' => 'ph-arrows-left-right'],
            'membros' => ['label' => 'Membros', 'icon' => 'ph-users-three'],
            'financeiro' => ['label' => 'Financeiro', 'icon' => 'ph-currency-circle-dollar'],
            'operacao' => ['label' => 'Operação', 'icon' => 'ph-hourglass-medium'],
        ];

        $mostraGeral = $tipoRelatorio === 'visao_geral';
        $mostraAcervo = in_array($tipoRelatorio, ['visao_geral', 'acervo'], true);
        $mostraCirculacao = in_array($tipoRelatorio, ['visao_geral', 'circulacao'], true);
        $mostraMembros = in_array($tipoRelatorio, ['visao_geral', 'membros'], true);
        $mostraFinanceiro = in_array($tipoRelatorio, ['visao_geral', 'financeiro'], true);
        $mostraOperacao = in_array($tipoRelatorio, ['visao_geral', 'operacao'], true);
    @endphp

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
                    <h1 class="text-lg font-black text-slate-900 dark:text-white">Painel Relatórios</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Acervo, circulação, atrasos e multas</p>
                </div>
            </div>
            <button type="button" @click="dark = !dark" class="w-9 h-9 rounded-md bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition">
                <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
            </button>
        </div>
    </x-slot>

    <style>
        .reports-bg {
            background:
                radial-gradient(circle at top left, rgba(30,58,138,.15), transparent 34rem),
                radial-gradient(circle at bottom right, rgba(245,158,11,.24), transparent 30rem),
                linear-gradient(180deg, #dbe7f5 0%, #edf2f8 46%, #dfe8f2 100%);
        }
        .dark .reports-bg {
            background:
                radial-gradient(circle at top left, rgba(30,58,138,.20), transparent 32rem),
                radial-gradient(circle at bottom right, rgba(245,158,11,.10), transparent 28rem),
                #0f172a;
        }
        .reports-panel {
            background: rgba(255,255,255,.88);
            border-color: rgba(100,116,139,.36);
            box-shadow: 0 18px 45px rgba(15,23,42,.10);
        }
        .dark .reports-panel {
            background: #111827;
            border-color: #1e293b;
            box-shadow: none;
        }
        .reports-table-head {
            background: #dbeafe;
            color: #334155;
        }
        .dark .reports-table-head {
            background: rgba(2,6,23,.60);
            color: #94a3b8;
        }
        .chart-box {
            height: 250px;
            position: relative;
        }
        .chart-box-lg { height: 300px; }
        .reports-input {
            width: 100%;
            height: 42px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            font-size: .86rem;
            padding: 0 12px;
            color-scheme: light;
        }
        .dark .reports-input {
            border-color: #334155;
            background: #0b1220;
            color: #e2e8f0;
            color-scheme: dark;
        }
        .reports-filter-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 150px 150px 92px 86px;
            gap: 12px;
            align-items: end;
        }
        .book-ranking-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 44px;
            gap: 12px;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(148,163,184,.24);
        }
        .book-ranking-row:last-child { border-bottom: 0; }
        .ranking-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 28px;
            border-radius: 6px;
            background: #fef3c7;
            color: #92400e;
            font-weight: 900;
            font-size: .78rem;
        }
        .dark .ranking-count {
            background: rgba(245,158,11,.14);
            color: #fbbf24;
        }
        @media (max-width: 1024px) {
            .reports-filter-grid { grid-template-columns: 1fr 1fr; }
            .reports-filter-copy { grid-column: 1 / -1; }
        }
        @media (max-width: 640px) {
            .reports-filter-grid { grid-template-columns: 1fr; }
        }
        .reports-table-wrap {
            overflow-x: auto;
            padding-bottom: .5rem;
            overscroll-behavior-x: contain;
            scrollbar-width: thin;
            scrollbar-color: #F59E0B rgba(148,163,184,.25);
        }
        .reports-table-wrap::-webkit-scrollbar { height: 9px; }
        .reports-table-wrap::-webkit-scrollbar-track { background: rgba(148,163,184,.20); border-radius: 999px; }
        .reports-table-wrap::-webkit-scrollbar-thumb { background: #F59E0B; border-radius: 999px; }
        .report-icon { color: rgba(245,158,11,.38); }
        .dark .report-icon { color: rgba(245,158,11,.20); }
    </style>

    <div class="-mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 min-h-screen reports-bg relative overflow-hidden">
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="bg-dots-relatorios" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.10"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#bg-dots-relatorios)"/>
            </svg>
            <i class="ph ph-books report-icon absolute left-[8%] top-[10%] text-[32px]"></i>
            <i class="ph ph-chart-line-up report-icon absolute right-[12%] top-[18%] text-[28px]"></i>
            <i class="ph ph-currency-circle-dollar report-icon absolute left-[18%] bottom-[18%] text-[30px]"></i>
            <i class="ph ph-calendar-check report-icon absolute right-[20%] bottom-[12%] text-[26px]"></i>
        </div>

        <div class="max-w-7xl mx-auto relative z-10 space-y-6">
            <form method="GET" class="reports-panel border rounded-lg p-4 reports-filter-grid">
                <input type="hidden" name="tipo" value="{{ $tipoRelatorio }}">
                <div class="reports-filter-copy">
                    <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black mb-1">Período analisado</p>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Os relatórios abaixo usam empréstimos entre {{ $inicio->format('d/m/Y') }} e {{ $fim->format('d/m/Y') }}.</p>
                </div>
                <div>
                    <label for="inicio" class="block text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold mb-1">Início</label>
                    <input id="inicio" name="inicio" type="date" value="{{ $inicio->toDateString() }}" min="2000-01-01" max="{{ today()->toDateString() }}" class="reports-input">
                </div>
                <div>
                    <label for="fim" class="block text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold mb-1">Fim</label>
                    <input id="fim" name="fim" type="date" value="{{ $fim->toDateString() }}" min="2000-01-01" max="{{ today()->toDateString() }}" class="reports-input">
                </div>
                <button class="h-[42px] px-4 rounded-md bg-[#1E3A8A] text-white text-xs font-black uppercase tracking-wider hover:bg-blue-900 transition inline-flex items-center justify-center">
                    <i class="ph ph-funnel mr-1"></i> Filtrar
                </button>
                <a href="{{ route('admin.relatorios.pdf', ['inicio' => $inicio->toDateString(), 'fim' => $fim->toDateString(), 'tipo' => $tipoRelatorio]) }}" class="h-[42px] px-4 rounded-md bg-[#F59E0B] text-slate-950 text-xs font-black uppercase tracking-wider hover:bg-amber-400 transition inline-flex items-center justify-center">
                    <i class="ph ph-file-pdf mr-1"></i> PDF
                </a>
            </form>

            <nav class="reports-panel flex gap-2 overflow-x-auto rounded-lg border p-2" aria-label="Relatórios separados">
                @foreach($relatorios as $tipo => $relatorio)
                    <a href="{{ route('admin.relatorios.index', ['inicio' => $inicio->toDateString(), 'fim' => $fim->toDateString(), 'tipo' => $tipo]) }}" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-md px-3 text-[11px] font-black uppercase tracking-widest transition {{ $tipoRelatorio === $tipo ? 'bg-[#1E3A8A] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10' }}">
                        <i class="ph {{ $relatorio['icon'] }}"></i>
                        {{ $relatorio['label'] }}
                    </a>
                @endforeach
            </nav>

            @if($mostraGeral)
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach([
                    ['label' => 'Livros no acervo', 'value' => $metricas['livros'], 'icon' => 'ph-books', 'tone' => 'text-blue-600 dark:text-blue-400'],
                    ['label' => 'Exemplares', 'value' => $metricas['exemplares'], 'icon' => 'ph-stack', 'tone' => 'text-amber-600 dark:text-amber-400'],
                    ['label' => 'Empréstimos no período', 'value' => $metricas['emprestimosPeriodo'], 'icon' => 'ph-arrows-left-right', 'tone' => 'text-emerald-600 dark:text-emerald-400'],
                    ['label' => 'Multas registradas', 'value' => 'R$ '.number_format($metricas['multas'], 2, ',', '.'), 'icon' => 'ph-currency-circle-dollar', 'tone' => 'text-red-600 dark:text-red-400'],
                ] as $card)
                    <div class="reports-panel border rounded-lg p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-black">{{ $card['label'] }}</p>
                            <i class="ph {{ $card['icon'] }} {{ $card['tone'] }} text-xl"></i>
                        </div>
                        <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>
            @endif

            @if($mostraCirculacao)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <section class="reports-panel border rounded-lg p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Circulação por dia</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Quantidade de empréstimos no período filtrado.</p>
                        </div>
                        <i class="ph ph-chart-line-up text-amber-600 dark:text-amber-400 text-xl"></i>
                    </div>
                    <div class="chart-box chart-box-lg">
                        <canvas id="chartEmprestimosDia"></canvas>
                    </div>
                </section>

                <section class="reports-panel border rounded-lg p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Distribuição por status</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Visão rápida do fluxo dos empréstimos.</p>
                        </div>
                        <i class="ph ph-chart-donut text-blue-700 dark:text-blue-400 text-xl"></i>
                    </div>
                    <div class="chart-box chart-box-lg">
                        <canvas id="chartStatus"></canvas>
                    </div>
                </section>

                <section class="reports-panel border rounded-lg p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Ranking de livros</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Títulos com maior circulação.</p>
                        </div>
                        <i class="ph ph-chart-bar text-emerald-700 dark:text-emerald-400 text-xl"></i>
                    </div>
                    <div class="chart-box chart-box-lg">
                        <canvas id="chartLivros"></canvas>
                    </div>
                </section>

                <section class="reports-panel border rounded-lg p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Categorias</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Preferências mais fortes no período.</p>
                        </div>
                        <i class="ph ph-squares-four text-purple-700 dark:text-purple-400 text-xl"></i>
                    </div>
                    <div class="chart-box chart-box-lg">
                        <canvas id="chartCategorias"></canvas>
                    </div>
                </section>

                <section class="reports-panel border rounded-lg p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Sazonalidade</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Picos mensais de empréstimos no período.</p>
                        </div>
                        <i class="ph ph-calendar-dots text-amber-600 dark:text-amber-400 text-xl"></i>
                    </div>
                    <div class="chart-box chart-box-lg">
                        <canvas id="chartSazonalidade"></canvas>
                    </div>
                </section>
            </div>
            @endif

            @if($mostraGeral)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <section class="reports-panel border rounded-lg p-5">
                    <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Situação atual</p>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-300">Em uso</span>
                            <strong class="text-slate-950 dark:text-white">{{ $metricas['ativos'] }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-300">Atrasados</span>
                            <strong class="text-red-600 dark:text-red-400">{{ $metricas['atrasados'] }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-300">Membros</span>
                            <strong class="text-slate-950 dark:text-white">{{ $metricas['membros'] }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-300">Equipe cadastrada</span>
                            <strong class="text-slate-950 dark:text-white">{{ $metricas['bibliotecarios'] }}</strong>
                        </div>
                    </div>
                </section>

                <section class="reports-panel border rounded-lg p-5 lg:col-span-2">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Livros mais lidos</p>
                        <span class="text-xs text-slate-500">{{ $livrosMaisLidos->count() }} títulos</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse($livrosMaisLidos as $item)
                            <div class="book-ranking-row">
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $item['livro']->titulo }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-300">{{ $item['livro']->categoria }} @if($item['livro']->autor) · {{ $item['livro']->autor->nome }} @endif</p>
                                </div>
                                <span class="ranking-count">{{ $item['total'] }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum empréstimo encontrado no período.</p>
                        @endforelse
                    </div>
                </section>
            </div>
            @endif

            @if($mostraCirculacao)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <section class="reports-panel border rounded-lg p-5">
                    <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Categorias preferidas</p>
                    <div class="mt-4 space-y-3">
                        @forelse($categorias as $categoria)
                            @php
                                $percentual = $metricas['emprestimosPeriodo'] > 0 ? min(100, ($categoria['total'] / $metricas['emprestimosPeriodo']) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-bold text-slate-800 dark:text-white">{{ $categoria['categoria'] }}</span>
                                    <span class="text-slate-500">{{ $categoria['total'] }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-[#F59E0B]" style="width: {{ $percentual }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sem categorias no período.</p>
                        @endforelse
                    </div>
                </section>

                <section class="reports-panel border rounded-lg p-5">
                    <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Desempenho da equipe</p>
                    <div class="mt-4 space-y-3">
                        @forelse($desempenhoBibliotecarios as $item)
                            <div class="flex items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3 last:border-0 last:pb-0">
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $item['bibliotecario']->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['devolucoes'] }} devoluções concluídas</p>
                                </div>
                                <span class="text-lg font-black text-blue-700 dark:text-blue-400">{{ $item['atendimentos'] }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Nenhum atendimento aprovado no período.</p>
                        @endforelse
                    </div>
                </section>
            </div>
            @endif

            @if($mostraMembros)
            <section class="reports-panel border rounded-lg overflow-hidden">
                <div class="p-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Perfis de leitores</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Faixa etária, vínculo e categoria preferida com base nos empréstimos do período.</p>
                    </div>
                    <i class="ph ph-users-three text-blue-700 dark:text-blue-400 text-2xl"></i>
                </div>
                <div class="reports-table-wrap">
                    <table class="min-w-[780px] w-full text-sm">
                        <thead class="reports-table-head text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">Faixa etária</th>
                                <th class="text-left px-5 py-3">Tipo de membro</th>
                                <th class="text-left px-5 py-3">Leitores</th>
                                <th class="text-left px-5 py-3">Empréstimos</th>
                                <th class="text-left px-5 py-3">Categoria preferida</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($perfisLeitores as $perfil)
                                <tr class="hover:bg-slate-100/70 dark:hover:bg-white/[.03]">
                                    <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">{{ $perfil['faixa_etaria'] }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $perfil['tipo_membro'] }}</td>
                                    <td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ $perfil['leitores'] }}</td>
                                    <td class="px-5 py-3 text-amber-700 dark:text-amber-300 font-bold">{{ $perfil['emprestimos'] }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                                        {{ $perfil['categoria_preferida'] }}
                                        <span class="text-xs text-slate-500 dark:text-slate-400">({{ $perfil['categoria_total'] }})</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">Nenhum perfil de leitor encontrado no período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            @if($mostraCirculacao)
            <section class="reports-panel border rounded-lg overflow-hidden">
                <div class="p-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Sazonalidade por categoria</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Meses e categorias com maior procura no período filtrado.</p>
                    </div>
                    <i class="ph ph-trend-up text-emerald-700 dark:text-emerald-400 text-2xl"></i>
                </div>
                <div class="reports-table-wrap">
                    <table class="min-w-[720px] w-full text-sm">
                        <thead class="reports-table-head text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">Mês</th>
                                <th class="text-left px-5 py-3">Categoria</th>
                                <th class="text-left px-5 py-3">Empréstimos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($sazonalidade as $item)
                                <tr class="hover:bg-slate-100/70 dark:hover:bg-white/[.03]">
                                    <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">{{ $item['mes'] }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $item['categoria'] }}</td>
                                    <td class="px-5 py-3 text-amber-700 dark:text-amber-300 font-bold">{{ $item['total'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">Nenhuma sazonalidade encontrada no período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            @if($mostraOperacao)
            <section class="reports-panel border rounded-lg overflow-hidden">
                <div class="p-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Reservas em fila</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Títulos aguardando atendimento e tempo médio de espera.</p>
                    </div>
                    <i class="ph ph-hourglass-medium text-purple-700 dark:text-purple-400 text-2xl"></i>
                </div>
                <div class="reports-table-wrap">
                    <table class="min-w-[860px] w-full text-sm">
                        <thead class="reports-table-head text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">Livro</th>
                                <th class="text-left px-5 py-3">Fila</th>
                                <th class="text-left px-5 py-3">Primeira reserva</th>
                                <th class="text-left px-5 py-3">Espera média</th>
                                <th class="text-left px-5 py-3">Membros na fila</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($reservasFila as $item)
                                <tr class="hover:bg-slate-100/70 dark:hover:bg-white/[.03]">
                                    <td class="px-5 py-3">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $item['livro']->titulo }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['livro']->categoria }} @if($item['livro']->autor) · {{ $item['livro']->autor->nome }} @endif</p>
                                    </td>
                                    <td class="px-5 py-3 text-amber-700 dark:text-amber-300 font-black">{{ $item['fila'] }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $item['primeira_reserva']?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ $item['espera_media'] }} dias</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $item['membros']->join(', ') ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">Nenhuma reserva ativa em fila.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            @if($mostraOperacao)
            <section class="reports-panel border rounded-lg overflow-hidden">
                <div class="p-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Sugestões de compra</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Prioridade calculada por reservas em fila, disponibilidade e circulação no período.</p>
                    </div>
                    <i class="ph ph-shopping-cart-simple text-amber-600 dark:text-amber-400 text-2xl"></i>
                </div>
                <div class="reports-table-wrap">
                    <table class="min-w-[980px] w-full text-sm">
                        <thead class="reports-table-head text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">Livro</th>
                                <th class="text-left px-5 py-3">Motivo</th>
                                <th class="text-left px-5 py-3">Prioridade</th>
                                <th class="text-left px-5 py-3">Reservas</th>
                                <th class="text-left px-5 py-3">Disponíveis</th>
                                <th class="text-left px-5 py-3">Circulação</th>
                                <th class="text-left px-5 py-3">Sugerido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($sugestoesCompra as $item)
                                @php
                                    $priorityClass = match ($item['prioridade']) {
                                        'Alta' => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-300 dark:border-red-500/30',
                                        'Média' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/30',
                                        default => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:border-blue-500/30',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-100/70 dark:hover:bg-white/[.03]">
                                    <td class="px-5 py-3">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $item['livro']->titulo }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item['livro']->categoria }} @if($item['livro']->autor) · {{ $item['livro']->autor->nome }} @endif</p>
                                    </td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $item['motivo'] }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center rounded-md border px-2 py-1 text-xs font-black {{ $priorityClass }}">{{ $item['prioridade'] }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ $item['reservas'] }}</td>
                                    <td class="px-5 py-3 text-emerald-700 dark:text-emerald-300 font-bold">{{ $item['disponiveis'] }}</td>
                                    <td class="px-5 py-3 text-amber-700 dark:text-amber-300 font-bold">{{ $item['circulacao'] }}</td>
                                    <td class="px-5 py-3 text-slate-900 dark:text-white font-black">{{ $item['quantidade_sugerida'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">Nenhuma sugestão de compra no período filtrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            @if($mostraAcervo)
            <section class="reports-panel border rounded-lg overflow-hidden">
                <div class="p-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Acervo completo</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Disponibilidade calculada pelos empréstimos em uso.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" data-scroll-table="acervo" data-direction="-1" class="w-9 h-9 rounded-md border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-amber-500 hover:text-amber-600 transition">
                            <i class="ph ph-caret-left"></i>
                        </button>
                        <button type="button" data-scroll-table="acervo" data-direction="1" class="w-9 h-9 rounded-md border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-amber-500 hover:text-amber-600 transition">
                            <i class="ph ph-caret-right"></i>
                        </button>
                    </div>
                </div>
                <div id="table-acervo" class="reports-table-wrap">
                    <table class="min-w-[1040px] w-full text-sm">
                        <thead class="reports-table-head text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">Livro</th>
                                <th class="text-left px-5 py-3">Categoria</th>
                                <th class="text-left px-5 py-3">Autor</th>
                                <th class="text-left px-5 py-3">Estante</th>
                                <th class="text-left px-5 py-3">Localização</th>
                                <th class="text-left px-5 py-3">Total</th>
                                <th class="text-left px-5 py-3">Emprestados</th>
                                <th class="text-left px-5 py-3">Disponíveis</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($acervo as $item)
                                <tr class="hover:bg-slate-100/70 dark:hover:bg-white/[.03]">
                                    <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">{{ $item['livro']->titulo }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $item['livro']->categoria }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $item['livro']->autor->nome ?? 'Sem autor' }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $item['livro']->estante ?: '—' }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $item['livro']->localizacao ?: '—' }}</td>
                                    <td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ $item['livro']->quantidade }}</td>
                                    <td class="px-5 py-3 text-amber-700 dark:text-amber-300 font-bold">{{ $item['emprestados'] }}</td>
                                    <td class="px-5 py-3 text-emerald-700 dark:text-emerald-300 font-bold">{{ $item['disponiveis'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            @if($mostraFinanceiro)
            <section class="reports-panel border rounded-lg overflow-hidden">
                <div class="p-5">
                    <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Atrasos e multas</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Lista focada para cobrança e acompanhamento.</p>
                </div>
                <div class="reports-table-wrap">
                    <table class="min-w-[850px] w-full text-sm">
                        <thead class="reports-table-head text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">Membro</th>
                                <th class="text-left px-5 py-3">Livro</th>
                                <th class="text-left px-5 py-3">Previsto</th>
                                <th class="text-left px-5 py-3">Dias</th>
                                <th class="text-left px-5 py-3">Multa prevista</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($atrasados as $emprestimo)
                                @php
                                    $dias = (int) $emprestimo->data_devolucao_prevista->copy()->startOfDay()->diffInDays(now()->startOfDay());
                                    $multa = \App\Models\Emprestimos::calcularMulta($emprestimo->data_devolucao_prevista);
                                @endphp
                                <tr>
                                    <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">{{ $emprestimo->membro->nome ?? 'Membro removido' }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $emprestimo->livro->titulo ?? 'Livro removido' }}</td>
                                    <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $emprestimo->data_devolucao_prevista?->format('d/m/Y') }}</td>
                                    <td class="px-5 py-3 text-red-600 dark:text-red-400 font-bold">{{ $dias }}</td>
                                    <td class="px-5 py-3 text-red-600 dark:text-red-400 font-bold">R$ {{ number_format($multa, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">Nenhum empréstimo atrasado agora.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = @json($graficos);
        const chartColors = ['#1E3A8A', '#F59E0B', '#10B981', '#EF4444', '#7C3AED', '#0EA5E9', '#F97316', '#14B8A6'];
        const isDarkMode = () => document.documentElement.classList.contains('dark');
        const gridColor = () => isDarkMode() ? 'rgba(148,163,184,.20)' : 'rgba(51,65,85,.18)';
        const labelColor = () => isDarkMode() ? '#E2E8F0' : '#334155';
        const subtleLabelColor = () => isDarkMode() ? '#CBD5E1' : '#475569';
        const charts = [];

        function makeChart(canvasId, config) {
            const canvas = document.getElementById(canvasId);
            if (!canvas || typeof Chart === 'undefined') return null;
            const chart = new Chart(canvas, config);
            charts.push(chart);
            return chart;
        }

        const sharedScales = () => ({
            x: {
                ticks: { color: labelColor(), maxRotation: 0, autoSkip: true },
                grid: { color: gridColor() },
            },
            y: {
                beginAtZero: true,
                ticks: { color: labelColor(), precision: 0 },
                grid: { color: gridColor() },
            },
        });

        makeChart('chartEmprestimosDia', {
            type: 'line',
            data: {
                labels: chartData.diasLabels,
                datasets: [{
                    label: 'Empréstimos',
                    data: chartData.diasValores,
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245,158,11,.18)',
                    tension: .35,
                    fill: true,
                    pointRadius: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: labelColor() } } },
                scales: sharedScales(),
            },
        });

        makeChart('chartStatus', {
            type: 'doughnut',
            data: {
                labels: chartData.statusLabels,
                datasets: [{ data: chartData.statusValores, backgroundColor: chartColors, borderWidth: 0 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: labelColor(), boxWidth: 12 } } },
                cutout: '62%',
            },
        });

        makeChart('chartLivros', {
            type: 'bar',
            data: {
                labels: chartData.livrosLabels,
                datasets: [{ label: 'Leituras', data: chartData.livrosValores, backgroundColor: '#1E3A8A', borderRadius: 5 }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: sharedScales(),
            },
        });

        makeChart('chartCategorias', {
            type: 'bar',
            data: {
                labels: chartData.categoriasLabels,
                datasets: [{ label: 'Empréstimos', data: chartData.categoriasValores, backgroundColor: '#10B981', borderRadius: 5 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: sharedScales(),
            },
        });

        makeChart('chartSazonalidade', {
            type: 'line',
            data: {
                labels: chartData.sazonalidadeLabels,
                datasets: [{
                    label: 'Empréstimos',
                    data: chartData.sazonalidadeValores,
                    borderColor: '#0EA5E9',
                    backgroundColor: 'rgba(14,165,233,.16)',
                    tension: .35,
                    fill: true,
                    pointRadius: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: labelColor() } } },
                scales: sharedScales(),
            },
        });

        function refreshChartTheme() {
            charts.forEach((chart) => {
                if (chart.options.plugins?.legend?.labels) {
                    chart.options.plugins.legend.labels.color = labelColor();
                }

                if (chart.options.scales) {
                    Object.values(chart.options.scales).forEach((scale) => {
                        if (scale.ticks) scale.ticks.color = subtleLabelColor();
                        if (scale.grid) scale.grid.color = gridColor();
                    });
                }

                chart.update('none');
            });
        }

        new MutationObserver(refreshChartTheme).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        document.querySelectorAll('[data-scroll-table]').forEach((button) => {
            button.addEventListener('click', () => {
                const table = document.getElementById(`table-${button.dataset.scrollTable}`);
                const direction = Number(button.dataset.direction || 1);
                table?.scrollBy({ left: direction * 360, behavior: 'smooth' });
            });
        });
    </script>
</x-app-layout>
