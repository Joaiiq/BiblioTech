<x-app-layout>

    {{-- ══ LIBS ══ --}}
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <link  rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    
    {{-- ══ PHP DATA ══ --}}
    @php
        $totalLivros        = $livros->count();
        $totalAutores       = $autores->count();
        $emprestimosAtivos  = \App\Models\Emprestimos::whereNull('data_devolucao_real')->count();
        $totalMembros       = \App\Models\Membros::count();
        $dashboardUser = auth()->guard('web')->user();
        $dashboardMember = auth()->guard('membro')->user();
        $currentPessoa = $dashboardUser ?: $dashboardMember;
        $nomeCompleto = $currentPessoa ? ($currentPessoa->name ?? $currentPessoa->nome ?? 'Visitante') : 'Visitante';
        $primeiroNome = explode(' ', $nomeCompleto)[0];
        $hora     = now()->hour;
        $saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
        $isAdmin  = $dashboardUser && in_array($dashboardUser->tipo_usuario, ['gerente','bibliotecario'], true);
        $isMember = ! $dashboardUser && auth()->guard('membro')->check();
        $categorias = $livros->pluck('categoria')->filter()->unique()->sort()->values();
        $notifiableTop = $dashboardUser ?: $dashboardMember;
        $unreadCount = $notifiableTop ? $notifiableTop->unreadNotifications()->count() : 0;
        $hojeDashboard = now()->locale('pt_BR');
        $emprestimosHoje = \App\Models\Emprestimos::whereDate('data_emprestimo', today())->count();
        $devolucoesHoje = \App\Models\Emprestimos::whereIn('status', \App\Models\Emprestimos::STATUS_EM_ANDAMENTO)
            ->whereDate('data_devolucao_prevista', today())
            ->count();
        $atrasosAbertos = \App\Models\Emprestimos::whereIn('status', \App\Models\Emprestimos::STATUS_EM_ANDAMENTO)
            ->whereDate('data_devolucao_prevista', '<', today())
            ->count();
        $solicitacoesPendentes = \App\Models\Emprestimos::where('status', \App\Models\Emprestimos::STATUS_SOLICITADO)->count();
        $categoriaMaisViva = $categoriasMaisAcessadas->first()->categoria ?? $categorias->first() ?? 'Acervo';
        $livroDestaque = ($bestsellers->first() ?? $livrosRecentes->first() ?? $livros->first());
    @endphp

    {{-- ══════════════════════════════════════════════════════
         HEADER SLOT — topo compacto
         ══════════════════════════════════════════════════════ --}}
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between w-full">
            <div class="flex items-center gap-3 w-full sm:max-w-xl">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center gap-1 shrink-0">
                    <i class="ph ph-library text-[#1E3A8A] dark:text-blue-400 text-4xl"></i>
                    <div class="text-[11px] font-black tracking-tight text-center leading-tight">
                        <span class="text-[#1E3A8A] dark:text-blue-400">BIBLIO</span><br>
                        <span class="text-[#F59E0B]">TECH</span>
                    </div>
                </a>
                <div class="relative w-full">
                    <i class="ph ph-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-gray-500 text-sm"></i>
                    <input
                        id="top-filter"
                        type="text"
                        placeholder="Buscar titulo, autor..."
                        class="w-full bg-white dark:bg-[#0d1420] border border-slate-200 dark:border-white/10 rounded-lg pl-9 pr-3 py-2 text-xs text-slate-700 dark:text-gray-200 placeholder:text-slate-400 dark:placeholder:text-gray-600 focus:outline-none focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/30"
                        autocomplete="off"
                    >
                    <div id="global-search-panel" class="absolute left-0 right-0 top-[calc(100%+8px)] z-50 hidden overflow-hidden rounded-md border border-slate-200 bg-white shadow-2xl shadow-slate-950/10 dark:border-white/10 dark:bg-[#0d1420] dark:shadow-black/40">
                        <div class="border-b border-slate-200 px-3 py-2 dark:border-white/10">
                            <p class="text-[10px] font-black uppercase tracking-[.16em] text-slate-500 dark:text-slate-400">Resultados rápidos</p>
                        </div>
                        <div id="global-search-results" class="max-h-96 overflow-y-auto p-2"></div>
                        <div id="global-search-empty" class="hidden p-4 text-sm text-slate-500 dark:text-slate-400">
                            Nenhum resultado encontrado.
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-2">
                <div class="flex items-center gap-2">
                    <button type="button" @click="dark = !dark" class="w-9 h-9 rounded-lg bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition">
                        <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
                    </button>
                    <button type="button" id="notifications-toggle" class="relative w-9 h-9 rounded-lg bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition" aria-controls="notifications-sidebar" aria-label="Notificacoes">
                        <i class="ph ph-bell text-sm"></i>
                        @if($unreadCount)
                            <span id="notifications-badge" class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-black text-white bg-red-600 rounded-full">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </button>
                    @if($isMember)
                        <button type="button" id="loans-toggle" class="h-9 w-9 rounded-lg bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition flex items-center justify-center" aria-controls="loans-sidebar" aria-expanded="false" aria-label="Meus alugueis">
                            <i class="ph ph-ticket text-sm"></i>
                            <span class="sr-only">Meus alugueis</span>
                        </button>
                    @endif
                </div>

                @if($currentPessoa)
                <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-white/10">
                    <x-user-avatar :user="$currentPessoa" :name="$nomeCompleto" />
                </div>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- ══ CONTENT AREA ══ --}}
    <div class="-mx-4 px-4 py-10 bg-gradient-to-b from-slate-100 via-blue-50 to-slate-100 dark:from-[#0f172a] dark:via-[#0f172a] dark:to-[#0b1120] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">

    {{-- Dot grid --}}
    <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="bg-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                <circle id="bg-dot-circle" cx="1" cy="1" r="1" fill="#93c5fd" opacity="0.08"/>
            </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#bg-dots)"/>
    </svg>

    {{-- Glows --}}
    <div id="bg-glow-1" class="absolute -top-28 -left-20 w-96 h-96 rounded-full blur-[90px]"></div>
    <div id="bg-glow-2" class="absolute -bottom-20 -right-14 w-72 h-72 rounded-full blur-[80px]"></div>

    {{-- Prateleiras --}}
    <div class="bg-shelf absolute left-0 right-0 h-px top-[22%]"></div>
    <div class="bg-shelf absolute left-0 right-0 h-px top-[58%]"></div>

    {{-- Acento âmbar --}}
    <div class="absolute top-0 left-0 w-[3px] h-32 bg-[#F59E0B] opacity-50"></div>

    {{-- Ícones --}}
    <i class="ph ph-book bg-icon absolute left-[3%] top-[5%] text-[28px]"></i>
    <i class="ph ph-books bg-icon absolute left-[87%] top-[8%] text-[22px]"></i>
    <i class="ph ph-book-open bg-icon absolute left-[14%] top-[58%] text-[34px]"></i>
    <i class="ph ph-book-bookmark bg-icon absolute left-[74%] top-[54%] text-[26px]"></i>
    <i class="ph ph-bookmark bg-icon absolute left-[44%] top-[78%] text-[18px]"></i>
    <i class="ph ph-bookmarks bg-icon absolute left-[91%] top-[72%] text-[30px]"></i>
    <i class="ph ph-graduation-cap bg-icon absolute left-[59%] top-[12%] text-[24px]"></i>
    <i class="ph ph-scroll bg-icon absolute left-[29%] top-[30%] text-[16px]"></i>
    <i class="ph ph-library bg-icon absolute left-[68%] top-[36%] text-[28px]"></i>
    <i class="ph ph-notebook bg-icon absolute left-[80%] top-[22%] text-[20px]"></i>
    <i class="ph ph-book bg-icon absolute left-[8%] top-[80%] text-[22px]"></i>
    <i class="ph ph-book-open bg-icon absolute left-[50%] top-[44%] text-[14px]"></i>
</div>
    <div class="max-w-7xl mx-auto space-y-16 z-10">

        <div id="dashboard-home" class="space-y-16">
        <section class="gs-section">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-3 bg-white dark:bg-[#0d1420] border border-slate-200 dark:border-white/5 rounded-md overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,.65fr)]">
                        <div class="p-5 sm:p-6 lg:p-7">
                            <div class="flex flex-wrap items-center gap-2 mb-5">
                                <span class="inline-flex items-center gap-1.5 rounded-md border border-amber-300/70 bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[.16em] text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                                    <i class="ph ph-sun-horizon"></i>
                                    {{ $saudacao }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[.16em] text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-400">
                                    <i class="ph ph-calendar-blank"></i>
                                    {{ $hojeDashboard->translatedFormat('d \\d\\e F') }}
                                </span>
                            </div>

                            <p class="text-[10px] font-bold uppercase tracking-[.18em] text-blue-600 dark:text-blue-400 mb-1">
                                {{ $isAdmin ? 'Painel administrativo' : 'Sua biblioteca' }}
                            </p>
                            <h2 class="max-w-3xl text-2xl md:text-4xl font-black text-slate-950 dark:text-white font-serif leading-tight">
                                @if($isAdmin)
                                    {{ $primeiroNome }}, hoje o acervo pede atenção em circulação, prazos e fila de atendimento.
                                @else
                                    {{ $primeiroNome }}, encontre sua próxima leitura sem perder de vista seus prazos.
                                @endif
                            </h2>
                            <p class="max-w-2xl text-sm md:text-base text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                                @if($isAdmin)
                                    {{ $totalLivros }} títulos catalogados, {{ $totalMembros }} membros ativos no sistema e {{ $emprestimosAtivos }} empréstimos em acompanhamento agora.
                                @else
                                    {{ $totalLivros }} títulos disponíveis para explorar, favoritos para salvar e recomendações baseadas no seu jeito de ler.
                                @endif
                            </p>

                            <div class="mt-6 flex flex-wrap gap-3">
                                @if($isAdmin)
                                    <a href="{{ route('admin.emprestimos.index') }}" class="inline-flex items-center gap-2 rounded-md bg-[#1E3A8A] px-4 py-3 text-[11px] font-black uppercase tracking-widest text-white hover:bg-blue-800 transition">
                                        <i class="ph ph-arrows-left-right"></i>
                                        Revisar empréstimos
                                    </a>
                                    <a href="{{ route('membros.create') }}" class="inline-flex items-center gap-2 rounded-md border border-blue-300 bg-blue-50 px-4 py-3 text-[11px] font-black uppercase tracking-widest text-blue-700 hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20 transition">
                                        <i class="ph ph-user-plus"></i>
                                        Novo membro
                                    </a>
                                @else
                                    <a href="#acervo-section" class="inline-flex items-center gap-2 rounded-md bg-[#1E3A8A] px-4 py-3 text-[11px] font-black uppercase tracking-widest text-white hover:bg-blue-800 transition">
                                        <i class="ph ph-books"></i>
                                        Explorar acervo
                                    </a>
                                    @if($isMember)
                                        <a href="{{ route('membros.situacao') }}" class="inline-flex items-center gap-2 rounded-md border border-blue-300 bg-blue-50 px-4 py-3 text-[11px] font-black uppercase tracking-widest text-blue-700 hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20 transition">
                                            <i class="ph ph-user-focus"></i>
                                            Minha situação
                                        </a>
                                    @endif
                                @endif
                                <a href="#acervo-section" class="inline-flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-[11px] font-black uppercase tracking-widest text-amber-800 hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20 transition">
                                    <i class="ph ph-magnifying-glass"></i>
                                    Buscar livros
                                </a>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 bg-slate-50/80 p-5 sm:p-6 dark:border-white/5 dark:bg-white/[.03] lg:border-l lg:border-t-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">{{ $isAdmin ? 'Movimento de hoje' : 'Sua leitura agora' }}</p>
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $isAdmin ? 'Resumo para priorizar a operação.' : 'Resumo rápido do que merece sua atenção.' }}</p>
                                </div>
                                <i class="ph ph-chart-line-up text-2xl text-amber-600 dark:text-amber-400"></i>
                            </div>

                            @if($isAdmin)
                            <div class="mt-5 grid grid-cols-2 gap-3">
                                <div class="rounded-md border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-[#0d1420]">
                                    <p class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-500">Novos hoje</p>
                                    <p class="mt-1 text-2xl font-black text-slate-950 dark:text-white">{{ $emprestimosHoje }}</p>
                                </div>
                                <div class="rounded-md border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-[#0d1420]">
                                    <p class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-500">Devoluções</p>
                                    <p class="mt-1 text-2xl font-black text-slate-950 dark:text-white">{{ $devolucoesHoje }}</p>
                                </div>
                                <div class="rounded-md border border-red-200 bg-red-50 p-3 dark:border-red-500/20 dark:bg-red-500/10">
                                    <p class="text-[10px] uppercase tracking-widest text-red-600 dark:text-red-300">Atrasos</p>
                                    <p class="mt-1 text-2xl font-black text-red-700 dark:text-red-300">{{ $atrasosAbertos }}</p>
                                </div>
                                <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                                    <p class="text-[10px] uppercase tracking-widest text-amber-700 dark:text-amber-300">Solicitações</p>
                                    <p class="mt-1 text-2xl font-black text-amber-800 dark:text-amber-300">{{ $solicitacoesPendentes }}</p>
                                </div>
                            </div>
                            @else
                            <div class="mt-5 grid grid-cols-2 gap-3">
                                <div class="rounded-md border border-blue-200 bg-blue-50 p-3 dark:border-blue-500/20 dark:bg-blue-500/10">
                                    <p class="text-[10px] uppercase tracking-widest text-blue-700 dark:text-blue-300">Ativos</p>
                                    <p class="mt-1 text-2xl font-black text-blue-800 dark:text-blue-300">{{ $metricasMembro['ativos'] }}</p>
                                </div>
                                <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                                    <p class="text-[10px] uppercase tracking-widest text-amber-700 dark:text-amber-300">Reservas</p>
                                    <p class="mt-1 text-2xl font-black text-amber-800 dark:text-amber-300">{{ $metricasMembro['reservas_ativas'] }}</p>
                                </div>
                                <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                    <p class="text-[10px] uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Favoritos</p>
                                    <p class="mt-1 text-2xl font-black text-emerald-800 dark:text-emerald-300">{{ $metricasMembro['favoritos'] }}</p>
                                </div>
                                <div class="rounded-md border {{ $metricasMembro['atrasados'] > 0 ? 'border-red-200 bg-red-50 dark:border-red-500/20 dark:bg-red-500/10' : 'border-slate-200 bg-white dark:border-white/10 dark:bg-[#0d1420]' }} p-3">
                                    <p class="text-[10px] uppercase tracking-widest {{ $metricasMembro['atrasados'] > 0 ? 'text-red-600 dark:text-red-300' : 'text-slate-500 dark:text-slate-500' }}">Atrasos</p>
                                    <p class="mt-1 text-2xl font-black {{ $metricasMembro['atrasados'] > 0 ? 'text-red-700 dark:text-red-300' : 'text-slate-950 dark:text-white' }}">{{ $metricasMembro['atrasados'] }}</p>
                                </div>
                            </div>
                            @endif

                            <div class="mt-4 rounded-md border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-[#0d1420]">
                                <p class="text-[10px] font-black uppercase tracking-[.16em] text-slate-500 dark:text-slate-500">Sinal do acervo</p>
                                <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">
                                    {{ $categoriaMaisViva }} está puxando a prateleira.
                                </p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    @if($livroDestaque)
                                        Destaque para <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $livroDestaque->titulo }}</span>.
                                    @else
                                        Cadastre os primeiros livros para alimentar o painel.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($vitrinePrincipal)
                    <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,.85fr)] gap-6">
                        <section class="relative overflow-hidden rounded-md border border-amber-200 bg-white dark:border-amber-500/20 dark:bg-[#0d1420]">
                            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#1E3A8A] via-[#F59E0B] to-emerald-500"></div>
                            <div class="grid grid-cols-1 md:grid-cols-[210px_minmax(0,1fr)] gap-5 p-5 sm:p-6">
                                <a href="{{ route('livros.show', $vitrinePrincipal->id) }}" class="group block">
                                    <div class="relative mx-auto aspect-[3/4] w-44 overflow-hidden rounded-md bg-slate-100 shadow-xl shadow-slate-950/10 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10 md:w-full">
                                        @if($vitrinePrincipal->capa)
                                            <img src="{{ asset('storage/' . $vitrinePrincipal->capa) }}" alt="{{ $vitrinePrincipal->titulo }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-100 to-amber-50 dark:from-blue-950/50 dark:to-amber-950/20">
                                                <i class="ph ph-book-open-text text-5xl text-blue-700/50 dark:text-blue-300/40"></i>
                                            </div>
                                        @endif
                                        @if($vitrinePrincipal->e_bestseller)
                                            <span class="absolute left-3 top-3 rounded-md bg-[#F59E0B] px-2 py-1 text-[10px] font-black uppercase tracking-widest text-slate-950">
                                                Destaque
                                            </span>
                                        @endif
                                    </div>
                                </a>

                                <div class="flex min-w-0 flex-col justify-center">
                                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Vitrine da biblioteca</p>
                                    <h3 class="mt-2 text-2xl font-black leading-tight text-slate-950 dark:text-white font-serif md:text-3xl">
                                        {{ $vitrinePrincipal->titulo }}
                                    </h3>
                                    <p class="mt-1 text-sm font-semibold text-blue-700 dark:text-blue-300">
                                        {{ $vitrinePrincipal->autor->nome ?? 'Autor não informado' }}
                                    </p>
                                    <p class="mt-4 line-clamp-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                        {{ $vitrinePrincipal->sinopse ?: 'Um destaque selecionado do acervo para abrir a visita pela biblioteca.' }}
                                    </p>

                                    <div class="mt-5 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                                            <i class="ph ph-tag"></i>
                                            {{ $vitrinePrincipal->categoria ?? 'Acervo' }}
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                                            <i class="ph ph-stack"></i>
                                            {{ (int) $vitrinePrincipal->quantidade }} exemplares
                                        </span>
                                        @if(($reservasPorLivro[$vitrinePrincipal->id] ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                                                <i class="ph ph-bookmark-simple"></i>
                                                {{ $reservasPorLivro[$vitrinePrincipal->id] }} na fila
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-6 flex flex-wrap gap-3">
                                        <a href="{{ route('livros.show', $vitrinePrincipal->id) }}" class="inline-flex items-center gap-2 rounded-md bg-[#1E3A8A] px-4 py-3 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                            <i class="ph ph-eye"></i>
                                            Ver destaque
                                        </a>
                                        <a href="#acervo-section" class="inline-flex items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                            <i class="ph ph-books"></i>
                                            Ver prateleira
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="grid grid-cols-1 gap-3">
                            <div class="rounded-md border border-slate-200 bg-white p-4 dark:border-white/5 dark:bg-[#0d1420]">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Chegaram agora</p>
                                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Novidades</h3>
                                    </div>
                                    <i class="ph ph-sparkle text-xl text-amber-600 dark:text-amber-400"></i>
                                </div>
                                <div class="space-y-2">
                                    @forelse($vitrineNovidades as $livro)
                                        <a href="{{ route('livros.show', $livro->id) }}" class="group flex items-center gap-3 rounded-md border border-slate-200 bg-slate-50 p-2 transition hover:border-blue-300 hover:bg-blue-50 dark:border-white/10 dark:bg-white/[.03] dark:hover:border-blue-500/40 dark:hover:bg-blue-500/10">
                                            <div class="h-14 w-10 shrink-0 overflow-hidden rounded bg-slate-200 dark:bg-white/10">
                                                @if($livro->capa)
                                                    <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center">
                                                        <i class="ph ph-book text-slate-400"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-900 group-hover:text-blue-700 dark:text-white dark:group-hover:text-blue-300">{{ $livro->titulo }}</p>
                                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $livro->autor->nome ?? 'Autor não informado' }}</p>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="rounded-md border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">Cadastre mais livros para preencher as novidades.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="rounded-md border border-slate-200 bg-white p-4 dark:border-white/5 dark:bg-[#0d1420]">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Procura alta</p>
                                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Mais reservados</h3>
                                    </div>
                                    <i class="ph ph-bookmark-simple text-xl text-amber-600 dark:text-amber-400"></i>
                                </div>
                                <div class="space-y-2">
                                    @forelse($livrosMaisReservados as $livro)
                                        <a href="{{ route('livros.show', $livro->id) }}" class="flex items-center justify-between gap-3 rounded-md border border-amber-200 bg-amber-50 p-3 transition hover:bg-amber-100 dark:border-amber-500/20 dark:bg-amber-500/10 dark:hover:bg-amber-500/20">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $livro->titulo }}</p>
                                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $livro->categoria ?? 'Acervo' }}</p>
                                            </div>
                                            <span class="shrink-0 rounded-md bg-white px-2 py-1 text-[10px] font-black uppercase tracking-widest text-amber-800 dark:bg-[#0d1420] dark:text-amber-300">
                                                {{ $reservasPorLivro[$livro->id] ?? 0 }} fila
                                            </span>
                                        </a>
                                    @empty
                                        <p class="rounded-md border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">Quando houver reservas, os títulos mais disputados aparecem aqui.</p>
                                    @endforelse
                                </div>
                            </div>
                        </section>
                    </div>
                @endif

                @if($isMember)
                    <section class="lg:col-span-3 grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,.85fr)]">
                        <div class="rounded-md border border-blue-200 bg-white p-5 dark:border-blue-500/20 dark:bg-[#0d1420]">
                            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Meus pedidos</p>
                                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Empréstimos e reservas</h3>
                                </div>
                                <a href="{{ route('emprestimos.historico') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-blue-300 bg-blue-50 px-3 text-[10px] font-black uppercase tracking-widest text-blue-800 transition hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20">
                                    <i class="ph ph-clock-countdown"></i>
                                    Histórico
                                </a>
                            </div>

                            @if($emprestimosDoMembro->isEmpty() && $reservasDoMembro->isEmpty())
                                <div class="rounded-md border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">
                                    Você ainda não tem empréstimos ou reservas em andamento. Explore o acervo e solicite um livro quando encontrar algo interessante.
                                </div>
                            @else
                                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                    @foreach($emprestimosDoMembro as $emprestimo)
                                        @php
                                            $status = $emprestimo->status;
                                            $statusLabel = [
                                                \App\Models\Emprestimos::STATUS_APROVADO => 'Aguardando retirada',
                                                \App\Models\Emprestimos::STATUS_RETIRADO => 'Retirado',
                                                \App\Models\Emprestimos::STATUS_EM_USO => 'Em uso',
                                                \App\Models\Emprestimos::STATUS_DEVOLUCAO_SOLICITADA => 'Devolução solicitada',
                                            ][$status] ?? 'Em andamento';
                                            $atrasado = $emprestimo->isAtrasado();
                                            $venceHoje = $emprestimo->data_devolucao_prevista?->isToday();
                                        @endphp
                                        <article class="rounded-md border {{ $atrasado ? 'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' : ($venceHoje ? 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10' : 'border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/[.03]') }} p-3">
                                            <div class="flex gap-3">
                                                <div class="h-16 w-11 shrink-0 overflow-hidden rounded bg-slate-200 dark:bg-white/10">
                                                    @if($emprestimo->livro?->capa)
                                                        <img src="{{ asset('storage/' . $emprestimo->livro->capa) }}" alt="{{ $emprestimo->livro?->titulo }}" class="h-full w-full object-cover">
                                                    @else
                                                        <div class="flex h-full w-full items-center justify-center">
                                                            <i class="ph ph-book text-slate-400"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="rounded-md bg-white px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-slate-700 dark:bg-[#0d1420] dark:text-slate-300">{{ $statusLabel }}</span>
                                                        @if($atrasado)
                                                            <span class="rounded-md bg-red-600 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-white">Atrasado</span>
                                                        @elseif($venceHoje)
                                                            <span class="rounded-md bg-amber-500 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-slate-950">Hoje</span>
                                                        @endif
                                                    </div>
                                                    <a href="{{ route('livros.show', $emprestimo->livro_id) }}" class="mt-2 block truncate text-sm font-black text-slate-950 transition hover:text-blue-700 dark:text-white dark:hover:text-blue-300">{{ $emprestimo->livro?->titulo ?? 'Livro removido' }}</a>
                                                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $emprestimo->livro?->autor?->nome ?? 'Autor não informado' }}</p>
                                                    <p class="mt-2 text-xs font-bold text-slate-600 dark:text-slate-300">
                                                        @if($status === \App\Models\Emprestimos::STATUS_APROVADO)
                                                            Retire no balcão para iniciar o prazo.
                                                        @else
                                                            Prazo: {{ $emprestimo->data_devolucao_prevista?->format('d/m/Y') ?? 'a definir' }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach

                                    @foreach($reservasDoMembro as $reserva)
                                        <article class="rounded-md border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                                            <div class="flex gap-3">
                                                <div class="h-16 w-11 shrink-0 overflow-hidden rounded bg-white dark:bg-[#0d1420]">
                                                    @if($reserva->livro?->capa)
                                                        <img src="{{ asset('storage/' . $reserva->livro->capa) }}" alt="{{ $reserva->livro?->titulo }}" class="h-full w-full object-cover">
                                                    @else
                                                        <div class="flex h-full w-full items-center justify-center">
                                                            <i class="ph ph-bookmark-simple text-emerald-500"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="rounded-md bg-white px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-emerald-700 dark:bg-[#0d1420] dark:text-emerald-300">Reserva ativa</span>
                                                    <a href="{{ route('livros.show', $reserva->livro_id) }}" class="mt-2 block truncate text-sm font-black text-slate-950 transition hover:text-emerald-700 dark:text-white dark:hover:text-emerald-300">{{ $reserva->livro?->titulo ?? 'Livro removido' }}</a>
                                                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $reserva->livro?->autor?->nome ?? 'Autor não informado' }}</p>
                                                    <p class="mt-2 text-xs font-bold text-emerald-800 dark:text-emerald-200">A biblioteca avisará quando houver exemplar disponível.</p>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <aside class="rounded-md border border-slate-200 bg-white p-5 dark:border-white/5 dark:bg-[#0d1420]">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Alertas</p>
                                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">O que precisa de atenção</h3>
                                </div>
                                <i class="ph ph-bell-ringing text-xl text-amber-600 dark:text-amber-400"></i>
                            </div>

                            @if($alertasMembro->isEmpty())
                                <div class="rounded-md border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                                    <p class="text-sm font-black text-emerald-800 dark:text-emerald-200">Tudo tranquilo por aqui.</p>
                                    <p class="mt-1 text-xs leading-relaxed text-emerald-700/80 dark:text-emerald-100/70">Sem atrasos, multas ou retiradas pendentes no momento.</p>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach($alertasMembro->take(4) as $alerta)
                                        @php
                                            $alertClass = $alerta['tipo'] === 'danger'
                                                ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200'
                                                : ($alerta['tipo'] === 'warning'
                                                    ? 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100'
                                                    : 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200');
                                        @endphp
                                        <a href="{{ $alerta['url'] }}" class="block rounded-md border p-3 transition hover:translate-x-0.5 {{ $alertClass }}">
                                            <div class="flex gap-3">
                                                <i class="ph {{ $alerta['icone'] }} mt-0.5 text-lg"></i>
                                                <div>
                                                    <p class="text-sm font-black">{{ $alerta['titulo'] }}</p>
                                                    <p class="mt-1 text-xs leading-relaxed opacity-80">{{ $alerta['texto'] }}</p>
                                                    <p class="mt-2 text-[10px] font-black uppercase tracking-widest">{{ $alerta['acao'] }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </aside>
                    </section>

                    <section class="lg:col-span-3 rounded-md border border-amber-200 bg-white p-5 dark:border-amber-500/20 dark:bg-[#0d1420]">
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Quero ler</p>
                                <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Favoritos salvos</h3>
                            </div>
                            <a href="{{ route('favoritos.index') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-3 text-[10px] font-black uppercase tracking-widest text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20">
                                <i class="ph ph-heart"></i>
                                Abrir lista
                            </a>
                        </div>

                        @if($favoritosDoMembro->isEmpty())
                            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">
                                Nenhum favorito ainda. Abra uma obra e marque como Quero ler para ela aparecer aqui.
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach($favoritosDoMembro as $livro)
                                    <a href="{{ route('livros.show', $livro->id) }}" class="group flex items-center gap-3 rounded-md border border-slate-200 bg-slate-50 p-3 transition hover:border-amber-300 hover:bg-amber-50 dark:border-white/10 dark:bg-white/[.03] dark:hover:border-amber-500/40 dark:hover:bg-amber-500/10">
                                        <div class="h-16 w-11 shrink-0 overflow-hidden rounded bg-slate-200 dark:bg-white/10">
                                            @if($livro->capa)
                                                <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <i class="ph ph-book text-slate-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-slate-900 group-hover:text-amber-800 dark:text-white dark:group-hover:text-amber-300">{{ $livro->titulo }}</p>
                                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $livro->autor->nome ?? 'Autor não informado' }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="lg:col-span-3 rounded-md border border-blue-200 bg-white p-5 dark:border-blue-500/20 dark:bg-[#0d1420]">
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Sugestões para você</p>
                                <h3 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Próxima leitura</h3>
                            </div>
                            <a href="#acervo-section" class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-blue-300 bg-blue-50 px-3 text-[10px] font-black uppercase tracking-widest text-blue-800 transition hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20">
                                <i class="ph ph-compass"></i>
                                Explorar mais
                            </a>
                        </div>

                        @if($recomendados->isEmpty())
                            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">
                                As sugestões aparecem quando você pega livros emprestados ou salva títulos em Favoritos.
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($recomendados as $livro)
                                    <a href="{{ route('livros.show', $livro->id) }}" class="group grid grid-cols-[64px_minmax(0,1fr)] gap-3 rounded-md border border-slate-200 bg-slate-50 p-3 transition hover:border-blue-300 hover:bg-blue-50 dark:border-white/10 dark:bg-white/[.03] dark:hover:border-blue-500/40 dark:hover:bg-blue-500/10">
                                        <div class="h-24 w-16 overflow-hidden rounded bg-slate-200 dark:bg-white/10">
                                            @if($livro->capa)
                                                <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <i class="ph ph-book-open text-xl text-slate-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0 py-1">
                                            <div class="mb-2 flex flex-wrap gap-1.5">
                                                <span class="rounded-md bg-blue-100 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">{{ $livro->categoria ?? 'Acervo' }}</span>
                                                @if($livro->e_bestseller)
                                                    <span class="rounded-md bg-amber-100 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">Destaque</span>
                                                @endif
                                            </div>
                                            <p class="line-clamp-2 text-sm font-black leading-tight text-slate-950 group-hover:text-blue-800 dark:text-white dark:group-hover:text-blue-300">{{ $livro->titulo }}</p>
                                            <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $livro->autor->nome ?? 'Autor não informado' }}</p>
                                            <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $livro->sinopse ?: 'Uma sugestão do acervo baseada no seu perfil de leitura.' }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif

                <div class="lg:col-span-2 bg-white dark:bg-[#0d1420] border border-slate-200 dark:border-white/5 rounded-md p-5">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest">Livros</h3>
                        <span class="text-[10px] text-slate-500 dark:text-gray-500">Populares e recentes</span>
                    </div>

                    <div class="space-y-7">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-[11px] font-bold uppercase tracking-[.15em] text-amber-500">Populares</p>
                                <div class="flex items-center gap-2">
                                    <button id="swiper-populares-prev" class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-[#2563EB] hover:border-[#2563EB] hover:text-white dark:bg-white/5 dark:border-white/10 dark:text-gray-400 transition-all" aria-label="Anterior">
                                        <i class="ph ph-caret-left"></i>
                                    </button>
                                    <button id="swiper-populares-next" class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-[#2563EB] hover:border-[#2563EB] hover:text-white dark:bg-white/5 dark:border-white/10 dark:text-gray-400 transition-all" aria-label="Proximo">
                                        <i class="ph ph-caret-right"></i>
                                    </button>
                                    <a href="#acervo-section" class="px-3 py-2 rounded-lg text-[10px] text-amber-500 uppercase tracking-widest font-bold bg-amber-500/10 border border-amber-500/30 hover:bg-amber-500/20 hover:border-amber-500/50 transition-all">Ver mais</a>
                                </div>
                            </div>
                            <div class="swiper overflow-hidden" id="swiper-populares">
                                <div class="swiper-wrapper flex items-stretch">
                                    @php
                                        $populares = (isset($bestsellers) && $bestsellers->count()) ? $bestsellers->take(10) : $livros->take(10);
                                    @endphp
                                    @foreach($populares as $livro)
                                    <div class="swiper-slide h-auto shrink-0 !w-36 sm:!w-40 lg:!w-44">
                                        <a href="{{ route('livros.show', $livro->id) }}" class="group block">
                                            <div class="relative w-full h-52 rounded-xl overflow-hidden bg-slate-100 dark:bg-white/10">
                                                @if($livro->capa)
                                                    <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i class="ph ph-book text-2xl text-slate-400"></i>
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                                    <span class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest bg-white text-slate-900">Ver mais</span>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <p class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ $livro->titulo }}</p>
                                                <p class="text-[10px] text-slate-500 dark:text-gray-500 truncate">{{ $livro->autor->nome ?? '' }}</p>
                                            </div>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-[11px] font-bold uppercase tracking-[.15em] text-blue-500">Recentes</p>
                                <div class="flex items-center gap-2">
                                    <button id="swiper-recentes-prev" class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-[#2563EB] hover:border-[#2563EB] hover:text-white dark:bg-white/5 dark:border-white/10 dark:text-gray-400 transition-all" aria-label="Anterior">
                                        <i class="ph ph-caret-left"></i>
                                    </button>
                                    <button id="swiper-recentes-next" class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-[#2563EB] hover:border-[#2563EB] hover:text-white dark:bg-white/5 dark:border-white/10 dark:text-gray-400 transition-all" aria-label="Proximo">
                                        <i class="ph ph-caret-right"></i>
                                    </button>
                                    <a href="#acervo-section" class="px-3 py-2 rounded-lg text-[10px] text-blue-500 uppercase tracking-widest font-bold bg-blue-500/10 border border-blue-500/30 hover:bg-blue-500/20 hover:border-blue-500/50 transition-all">Ver mais</a>
                                </div>
                            </div>
                            <div class="swiper overflow-hidden" id="swiper-recentes">
                                <div class="swiper-wrapper flex items-stretch">
                                    @php
                                        $recentes = (isset($livrosRecentes) && $livrosRecentes->count()) ? $livrosRecentes->take(10) : $livros->take(10);
                                    @endphp
                                    @foreach($recentes as $livro)
                                    <div class="swiper-slide h-auto shrink-0 !w-36 sm:!w-40 lg:!w-44">
                                        <a href="{{ route('livros.show', $livro->id) }}" class="group block">
                                            <div class="relative w-full h-52 rounded-xl overflow-hidden bg-slate-100 dark:bg-white/10">
                                                @if($livro->capa)
                                                    <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i class="ph ph-book text-2xl text-slate-400"></i>
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                                    <span class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest bg-white text-slate-900">Ver mais</span>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <p class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ $livro->titulo }}</p>
                                                <p class="text-[10px] text-slate-500 dark:text-gray-500 truncate">{{ $livro->autor->nome ?? '' }}</p>
                                            </div>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1 bg-white dark:bg-[#0d1420] border border-slate-200 dark:border-white/5 rounded-md p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest">Categorias</h3>
                        <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Mais acessadas</span>
                    </div>
                    @php
                        $categoriaIcones = [
                            'Romance' => 'ph-heart',
                            'Aventura' => 'ph-compass',
                            'Fantasia' => 'ph-magic-wand',
                            'Ficcao Cientifica' => 'ph-planet',
                            'Ficcao' => 'ph-mask-happy',
                            'Biografia' => 'ph-pen-nib',
                            'Historia' => 'ph-scroll',
                            'Autoajuda' => 'ph-lightbulb-filament',
                            'Didatico' => 'ph-graduation-cap',
                            'Terror' => 'ph-ghost',
                            'Poesia' => 'ph-feather',
                            'HQ/Comic' => 'ph-chat-circle-text',
                            'Outros' => 'ph-book',
                            'Ciencia' => 'ph-atom',
                            'Tecnologia' => 'ph-cpu',
                            'Literatura' => 'ph-book-open-text',
                            'Natureza' => 'ph-leaf',
                        ];
                        $categoriaCores = [
                            'Romance' => 'border-rose-300 bg-rose-50 text-rose-800 hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-900/25 dark:text-rose-200 dark:hover:bg-rose-900/35',
                            'Aventura' => 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-900/20 dark:text-amber-200 dark:hover:bg-amber-900/30',
                            'Fantasia' => 'border-violet-300 bg-violet-50 text-violet-800 hover:bg-violet-100 dark:border-violet-900/50 dark:bg-violet-900/20 dark:text-violet-200 dark:hover:bg-violet-900/30',
                            'Ficcao Cientifica' => 'border-sky-300 bg-sky-50 text-sky-800 hover:bg-sky-100 dark:border-sky-900/50 dark:bg-sky-900/20 dark:text-sky-200 dark:hover:bg-sky-900/30',
                            'Ficcao' => 'border-fuchsia-300 bg-fuchsia-50 text-fuchsia-800 hover:bg-fuchsia-100 dark:border-fuchsia-900/50 dark:bg-fuchsia-900/20 dark:text-fuchsia-200 dark:hover:bg-fuchsia-900/30',
                            'Biografia' => 'border-orange-300 bg-orange-50 text-orange-800 hover:bg-orange-100 dark:border-orange-900/50 dark:bg-orange-900/20 dark:text-orange-200 dark:hover:bg-orange-900/30',
                            'Historia' => 'border-stone-300 bg-stone-50 text-stone-800 hover:bg-stone-100 dark:border-stone-900/50 dark:bg-stone-900/20 dark:text-stone-200 dark:hover:bg-stone-900/30',
                            'Autoajuda' => 'border-lime-300 bg-lime-50 text-lime-800 hover:bg-lime-100 dark:border-lime-900/50 dark:bg-lime-900/20 dark:text-lime-200 dark:hover:bg-lime-900/30',
                            'Didatico' => 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-900/20 dark:text-emerald-200 dark:hover:bg-emerald-900/30',
                            'Terror' => 'border-purple-300 bg-purple-50 text-purple-800 hover:bg-purple-100 dark:border-purple-950/50 dark:bg-purple-950/30 dark:text-purple-200 dark:hover:bg-purple-950/40',
                            'Poesia' => 'border-indigo-300 bg-indigo-50 text-indigo-800 hover:bg-indigo-100 dark:border-indigo-900/50 dark:bg-indigo-900/20 dark:text-indigo-200 dark:hover:bg-indigo-900/30',
                            'HQ/Comic' => 'border-teal-300 bg-teal-50 text-teal-800 hover:bg-teal-100 dark:border-teal-900/50 dark:bg-teal-900/20 dark:text-teal-200 dark:hover:bg-teal-900/30',
                            'Outros' => 'border-slate-300 bg-slate-50 text-slate-800 hover:bg-slate-100 dark:border-slate-800/60 dark:bg-slate-900/30 dark:text-slate-200 dark:hover:bg-slate-900/40',
                            'Ciencia' => 'border-cyan-300 bg-cyan-50 text-cyan-800 hover:bg-cyan-100 dark:border-cyan-900/50 dark:bg-cyan-900/20 dark:text-cyan-200 dark:hover:bg-cyan-900/30',
                            'Tecnologia' => 'border-blue-300 bg-blue-50 text-blue-800 hover:bg-blue-100 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-200 dark:hover:bg-blue-900/30',
                            'Literatura' => 'border-yellow-300 bg-yellow-50 text-yellow-800 hover:bg-yellow-100 dark:border-yellow-900/50 dark:bg-yellow-900/20 dark:text-yellow-200 dark:hover:bg-yellow-900/30',
                            'Natureza' => 'border-green-300 bg-green-50 text-green-800 hover:bg-green-100 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-200 dark:hover:bg-green-900/30',
                        ];
                    @endphp
                    @if(isset($categoriasMaisAcessadas) && $categoriasMaisAcessadas->count())
                        <div class="flex flex-col gap-2">
                            @foreach($categoriasMaisAcessadas as $cat)
                                @php
                                    $nomeCat = $cat->categoria ?? 'Outros';
                                    $nomeCatKey = \Illuminate\Support\Str::ascii($nomeCat);
                                    $icone = $categoriaIcones[$nomeCatKey] ?? $categoriaIcones[$nomeCat] ?? 'ph-book';
                                    $cor = $categoriaCores[$nomeCatKey] ?? $categoriaCores[$nomeCat] ?? 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100';
                                @endphp
                                <button type="button" data-cat-filter="{{ $nomeCat }}" class="w-full flex items-center justify-between gap-3 rounded-md border px-3 py-2 text-left transition {{ $cor }}">
                                    <span class="flex items-center gap-2">
                                        <i class="ph {{ $icone }}"></i>
                                        <span class="text-xs font-semibold">{{ $nomeCat }}</span>
                                    </span>
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $cat->total }}</span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-slate-500 dark:text-gray-500 text-sm">
                            Ainda não há acessos suficientes.
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ── Autores em Destaque ── --}}
        <section class="gs-section">
            <div class="flex items-end justify-between mb-7">
                <div class="flex items-end gap-4">
                    <div class="">
                        <p class="text-[10px] font-bold uppercase tracking-[.15em] text-blue-500 mb-1">Criadores</p>
                        <h2 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white font-serif">Autores em Destaque</h2>
                    </div>
                </div>
                <div class="flex gap-2 pb-1">
                    <button id="swiper-autores-prev" class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-[#2563EB] hover:border-[#2563EB] hover:text-white dark:bg-white/5 dark:border-white/10 dark:text-gray-400 transition-all"><i class="ph ph-caret-left"></i></button>
                    <button id="swiper-autores-next" class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-[#2563EB] hover:border-[#2563EB] hover:text-white dark:bg-white/5 dark:border-white/10 dark:text-gray-400 transition-all"><i class="ph ph-caret-right"></i></button>
                </div>
            </div>
            <div class="swiper swiperAutores pt-[42px] overflow-hidden">
                <div class="swiper-wrapper flex items-stretch">
                    @foreach($autores as $autor)
                    <div class="swiper-slide h-auto">
                        <div class="group bg-white dark:bg-[#0d1420] rounded-xl border border-slate-200 dark:border-white/5 hover:border-[#2563EB]/40 transition-all flex flex-col text-center pb-4 px-4 h-full">
                            <div class="shrink-0 w-[68px] h-[68px] rounded-full overflow-hidden border-2 border-blue-600/50 bg-[#111827] -mt-[34px] mx-auto mb-2.5 relative z-10">
                                @if($autor->foto)
                                    <img src="{{ asset('storage/' . $autor->foto) }}" class="w-full h-full object-cover" alt="{{ $autor->nome }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-[#111827]"><i class="ph ph-user text-2xl text-gray-600"></i></div>
                                @endif
                            </div>
                            <a href="{{ route('autores.show', $autor->id) }}" class="flex-grow flex flex-col">
                                <h4 class="text-slate-900 dark:text-white font-bold text-sm tracking-tight mt-1 group-hover:text-blue-400 transition-colors">{{ $autor->nome }}</h4>
                                <p class="text-[10px] uppercase tracking-widest text-slate-600 dark:text-gray-600 mt-0.5 mb-2">{{ $autor->nacionalidade ?? 'Não informado' }}</p>
                                <p class="text-slate-600 dark:text-gray-500 text-xs line-clamp-3 leading-relaxed px-1">{{ Str::limit($autor->biografia ?? 'Biografia não cadastrada.', 90) }}</p>
                            </a>
                            <div class="mt-3 pt-3 border-t border-slate-200 dark:border-white/5 flex items-center justify-between shrink-0">
                                <span class="text-[10px] text-slate-600 dark:text-gray-600 flex items-center gap-1">
                                    <i class="ph ph-books text-xs"></i>
                                    {{ $autor->livros_count }} {{ $autor->livros_count === 1 ? 'obra' : 'obras' }}
                                </span>
                                @if($isAdmin)
                                <div class="flex gap-3">
                                    <a href="{{ route('autores.edit', $autor->id) }}" class="text-gray-600 hover:text-[#F59E0B] transition"><i class="ph ph-pencil-simple text-xs"></i></a>
                                    <form action="{{ route('autores.destroy', $autor->id) }}" method="POST" class="form-delete inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn-delete text-gray-600 hover:text-red-400 transition"><i class="ph ph-trash text-xs"></i></button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        </div>

        {{-- ══ ACERVO (busca global) ══ --}}
        <section id="acervo-section" class="gs-section hidden">
            <div class="mb-6 rounded-md border border-slate-200 bg-white/95 p-4 shadow-sm dark:border-white/[.06] dark:bg-[#0d1420]/95 sm:p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/25">
                            <i class="ph ph-books text-xl"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-600 dark:text-emerald-400">Prateleira</p>
                            <h2 class="text-2xl font-black text-slate-950 dark:text-white font-serif md:text-3xl">Explore o acervo</h2>
                            <p class="mt-1 max-w-2xl text-sm text-slate-600 dark:text-slate-400">
                                Filtre por título, autor, categoria ou organize a estante para encontrar rápido o próximo livro.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span id="results-count" class="inline-flex h-10 items-center rounded-md border border-slate-200 bg-slate-50 px-3 text-[11px] font-black uppercase tracking-widest text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300"></span>
                        <button type="button" id="back-dashboard-btn" class="inline-flex h-10 items-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                            <i class="ph ph-arrow-left"></i>
                            Voltar ao painel
                        </button>
                    </div>
                </div>
            </div>

            <div class="relative z-30 mb-8 overflow-visible rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/[.06] dark:bg-[#0d1420]/95" id="filter-bar">
                <div class="border-b border-slate-200 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-white/[.03] sm:p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Busca refinada</p>
                            <h3 class="mt-1 font-serif text-xl font-black text-slate-950 dark:text-white">Encontre o livro certo</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Combine título, autor, categoria, disponibilidade e demanda da fila.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-quick-filter="disponivel" class="inline-flex h-9 items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 text-[10px] font-black uppercase tracking-widest text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">
                                <i class="ph ph-check-circle"></i>
                                Disponíveis
                            </button>
                            <button type="button" data-quick-filter="bestseller" class="inline-flex h-9 items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 text-[10px] font-black uppercase tracking-widest text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20">
                                <i class="ph ph-star"></i>
                                Destaques
                            </button>
                            <button type="button" data-quick-filter="fila" class="inline-flex h-9 items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-3 text-[10px] font-black uppercase tracking-widest text-blue-700 transition hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20">
                                <i class="ph ph-bookmark-simple"></i>
                                Com fila
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-12">
                    <div class="sm:col-span-2 lg:col-span-3 2xl:col-span-4">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400" for="filter-search">Buscar</label>
                        <div class="relative flex items-center">
                            <i class="ph ph-magnifying-glass pointer-events-none absolute left-3 z-10 text-base text-slate-400"></i>
                            <input type="text" id="filter-search" placeholder="Título ou autor..." class="h-11 w-full rounded-md border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-800 placeholder:text-slate-400 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200" autocomplete="off">
                        </div>
                    </div>
                    <div class="2xl:col-span-2">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400" for="filter-categoria">Categoria</label>
                        <select id="filter-categoria" placeholder="Todas...">
                            <option value="">Todas as categorias</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="2xl:col-span-2">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400" for="filter-autor">Autor</label>
                        <select id="filter-autor" placeholder="Todos...">
                            <option value="">Todos os autores</option>
                            @foreach($autores as $autor)
                            <option value="{{ $autor->id }}">{{ $autor->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="2xl:col-span-1">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400" for="filter-disponibilidade">Status</label>
                        <select id="filter-disponibilidade">
                            <option value="">Todos</option>
                            <option value="disponivel">Disponíveis</option>
                            <option value="indisponivel">Indisponíveis</option>
                        </select>
                    </div>
                    <div class="2xl:col-span-1">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400" for="filter-destaque">Sinal</label>
                        <select id="filter-destaque">
                            <option value="">Todos</option>
                            <option value="bestseller">Destaques</option>
                            <option value="fila">Com fila</option>
                        </select>
                    </div>
                    <div class="2xl:col-span-2">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400" for="filter-sort">Ordenar por</label>
                        <select id="filter-sort">
                            <option value="recente">Mais Recentes</option>
                            <option value="titulo_az">Título A → Z</option>
                            <option value="titulo_za">Título Z → A</option>
                            <option value="autor_az">Autor A → Z</option>
                            <option value="disponiveis">Mais disponíveis</option>
                            <option value="bestseller">Destaques</option>
                            <option value="mais_emprestados">Mais emprestados</option>
                            <option value="mais_reservados">Mais reservados</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex flex-col gap-3 border-t border-slate-200 pt-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                    <div id="active-filters" class="flex flex-wrap gap-2 hidden"></div>
                    <button id="clear-all-btn" class="hidden h-9 items-center justify-center gap-1.5 rounded-md border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                        <i class="ph ph-x"></i>
                        Limpar filtros
                    </button>
                </div>
                </div>
            </div>

            {{-- Prateleira principal --}}
            <div class="space-y-6">
                <div id="acervo-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($livros as $livro)
                    <div class="book-card acervo-card group bg-white dark:bg-[#0d1420] rounded-md overflow-hidden border border-slate-200 dark:border-white/5 hover:border-emerald-500/40 transition-[opacity,transform,border-color,box-shadow] duration-200 flex flex-col shadow-sm hover:shadow-lg hover:shadow-slate-950/10"
                         data-titulo="{{ strtolower($livro->titulo) }}"
                         data-autor-nome="{{ strtolower($livro->autor->nome ?? '') }}"
                         data-autor-id="{{ $livro->autor_id }}"
                         data-categoria="{{ $livro->categoria ?? 'Geral' }}"
                         data-bestseller="{{ $livro->e_bestseller ? '1' : '0' }}"
                         data-disponivel="{{ (int) $livro->quantidade > 0 ? '1' : '0' }}"
                         data-quantidade="{{ (int) $livro->quantidade }}"
                         data-reservas="{{ (int) ($reservasPorLivro[$livro->id] ?? 0) }}"
                         data-emprestimos="{{ (int) ($emprestimosPorLivro[$livro->id] ?? 0) }}"
                         data-data="{{ $livro->created_at?->timestamp ?? 0 }}">
                        <a href="{{ route('livros.show', $livro->id) }}" class="flex-grow flex flex-col">
                            <div class="relative h-64 overflow-hidden bg-slate-100 dark:bg-[#080d14]">
                                @if($livro->capa)
                                    <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 ease-[cubic-bezier(.4,0,.2,1)] group-hover:scale-[1.06]">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-emerald-50 to-blue-50 dark:from-emerald-900/20 dark:to-[#080d14]"><i class="ph ph-book text-4xl text-emerald-700/40 dark:text-emerald-300/30"></i></div>
                                @endif
                                <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-950/70 to-transparent"></div>
                                @if($livro->e_bestseller)
                                <div class="absolute top-3 left-3 px-2 py-1 rounded-md bg-amber-500 text-slate-900 text-[9px] font-black uppercase tracking-[.08em]">Destaque</div>
                                @endif
                                @if(($reservasPorLivro[$livro->id] ?? 0) > 0)
                                    <div class="absolute top-3 right-3 px-2 py-1 rounded-md bg-white/90 text-amber-800 text-[9px] font-black uppercase tracking-[.08em]">
                                        {{ $reservasPorLivro[$livro->id] }} fila
                                    </div>
                                @endif
                                <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between gap-2">
                                    <span class="truncate rounded-md bg-white/90 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-slate-800">{{ $livro->categoria ?? 'Geral' }}</span>
                                    <span class="shrink-0 rounded-md bg-emerald-500 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-white">{{ (int) $livro->quantidade }} ex.</span>
                                </div>
                            </div>
                            <div class="p-4 flex-grow flex flex-col gap-1">
                                <h4 class="text-slate-950 dark:text-white text-sm font-black leading-snug line-clamp-2 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ $livro->titulo }}</h4>
                                <p class="text-slate-600 dark:text-gray-500 text-xs truncate">{{ $livro->autor->nome ?? 'Autor não informado' }}</p>
                                <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $livro->sinopse ?: 'Detalhes disponíveis na página do livro.' }}</p>
                            </div>
                        </a>
                        @if($isAdmin)
                        <div class="px-4 pb-4 pt-3 border-t border-slate-200 dark:border-white/5 flex items-center justify-between shrink-0">
                            <a href="{{ route('livros.edit', $livro->id) }}" class="text-[10px] text-slate-600 hover:text-emerald-600 dark:text-gray-600 dark:hover:text-emerald-400 transition flex items-center gap-1"><i class="ph ph-pencil-simple"></i> Editar</a>
                            <form action="{{ route('livros.destroy', $livro->id) }}" method="POST" class="form-delete">
                                @csrf @method('DELETE')
                                <button type="button" class="btn-delete text-[10px] text-slate-600 hover:text-red-500 dark:text-gray-700 dark:hover:text-red-400 transition flex items-center gap-1"><i class="ph ph-trash"></i> Excluir</button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

            {{-- Empty state --}}
                <div id="empty-state" class="hidden rounded-md border border-slate-200 bg-white/90 p-8 text-center dark:border-white/[.06] dark:bg-[#0d1420]/90">
                    <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-[#0d1420] border border-slate-200 dark:border-white/[.06] flex items-center justify-center mx-auto mb-4">
                        <i class="ph ph-magnifying-glass text-2xl text-slate-500 dark:text-gray-700"></i>
                    </div>
                    <p class="text-slate-700 dark:text-gray-500 font-semibold text-sm">Nenhum livro encontrado</p>
                    <p class="text-slate-500 dark:text-gray-700 text-xs mt-1">Tente ajustar os filtros</p>
                    <button id="clear-filters-btn" class="mt-5 px-5 py-2 text-xs font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 rounded-lg hover:bg-emerald-500/10 transition">Limpar filtros</button>
                </div>
            </div>
        </section>

    </div>{{-- /max-w --}}
    </div>{{-- /content-area --}}

    @if($isMember)
    {{-- ══ SIDEBAR: MEUS ALUGUEIS ══ --}}
    <div id="loans-backdrop" class="fixed inset-0 bg-slate-950/40 opacity-0 pointer-events-none transition-opacity duration-200 z-50 dark:bg-slate-950/60" aria-hidden="true"></div>
    <aside id="loans-sidebar" class="fixed top-0 right-[-420px] w-[380px] max-w-[90vw] h-screen bg-white border-l border-slate-200 shadow-2xl transition-[right] duration-200 z-[60] flex flex-col dark:bg-[#0d1420] dark:border-white/10" role="dialog" aria-modal="true" aria-label="Meus alugueis">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between dark:border-white/10">
            <div>
                <h3 class="text-sm font-black text-slate-950 uppercase tracking-widest dark:text-white">Meus alugueis</h3>
                <p class="text-[11px] text-slate-500 dark:text-gray-400">Acompanhe seus prazos</p>
            </div>
            <button type="button" id="loans-close" class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition dark:bg-white/5 dark:border-white/10 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/10" aria-label="Fechar">
                <i class="ph ph-x text-sm"></i>
            </button>
        </div>
        <div class="p-5 overflow-y-auto flex-1">
            @if(isset($emprestimosDoMembro) && $emprestimosDoMembro->count())
                <div class="space-y-4">
                    @foreach($emprestimosDoMembro as $emp)
                        @php
                            $hoje = \Carbon\Carbon::today();
                            $inicio = $emp->data_emprestimo?->copy()->startOfDay();
                            $fim = $emp->data_devolucao_prevista?->copy()->startOfDay();
                            $total = ($inicio && $fim) ? max(1, $inicio->diffInDays($fim)) : 1;
                            $passado = $inicio ? max(0, $inicio->diffInDays($hoje, false)) : 0;
                            $progress = $fim ? min(100, round($passado / $total * 100)) : 0;
                            $diasRestantes = $fim ? $hoje->diffInDays($fim, false) : null;
                            $atrasado = $diasRestantes !== null && $diasRestantes < 0;
                            $progressClass = $atrasado ? 'bg-red-500' : ($progress > 75 ? 'bg-amber-500' : 'bg-blue-500');
                        @endphp
                        <div class="flex gap-3 rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                            <div class="w-12 h-16 rounded-md overflow-hidden bg-white flex items-center justify-center shrink-0 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10">
                                @if($emp->livro?->capa)
                                    <img src="{{ asset('storage/' . $emp->livro->capa) }}" alt="{{ $emp->livro?->titulo }}" class="w-full h-full object-cover">
                                @else
                                    <i class="ph ph-book text-sm text-slate-400"></i>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-950 truncate dark:text-white">{{ $emp->livro?->titulo ?? '—' }}</p>
                                    <span class="text-[10px] font-bold uppercase tracking-widest {{ $atrasado ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                        @if(! $fim)
                                            Pendente
                                        @else
                                            {{ $atrasado ? 'Vencido' : 'Ativo' }}
                                        @endif
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-gray-400">
                                    @if($fim)
                                        Expira em {{ $fim->format('d/m') }}
                                    @else
                                        Aguardando retirada
                                    @endif
                                </p>
                                <div class="mt-2 h-1.5 rounded-full bg-slate-200 overflow-hidden dark:bg-white/10">
                                    <div class="progress-fill {{ $progressClass }} h-full" data-progress="{{ $progress }}"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 text-slate-500 text-sm dark:text-gray-400">
                    Nenhum aluguel ativo.
                </div>
            @endif
        </div>
        <div class="p-5 border-t border-slate-200 dark:border-white/10">
            <a href="{{ route('emprestimos.historico') }}" class="w-full inline-flex items-center justify-center gap-2 h-10 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition text-[11px] font-bold uppercase tracking-widest dark:bg-white/5 dark:border-white/10 dark:text-gray-200 dark:hover:text-white dark:hover:bg-white/10">
                Ver historico completo
            </a>
        </div>
    </aside>
    @endif

    @if($notifiableTop)
    {{-- ══ SIDEBAR: NOTIFICAÇÕES (baseado em Meus alugueis) ══ --}}
    @php
        $notifiable = auth()->guard('web')->user() ?: auth()->guard('membro')->user();
        $unreads = $notifiable ? $notifiable->unreadNotifications()->latest()->get() : collect();
        $reads = $notifiable ? $notifiable->readNotifications()->latest()->take(30)->get() : collect();
    @endphp
    <div id="notifications-backdrop" class="fixed inset-0 bg-slate-950/40 opacity-0 pointer-events-none transition-opacity duration-200 z-50 dark:bg-slate-950/60" aria-hidden="true"></div>
    <aside id="notifications-sidebar" class="fixed top-0 right-[-420px] w-[380px] max-w-[90vw] h-screen bg-white border-l border-slate-200 shadow-2xl transition-[right] duration-200 z-[60] flex flex-col dark:bg-[#0d1420] dark:border-white/10" role="dialog" aria-modal="true" aria-label="Notificações">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between dark:border-white/10">
            <div>
                <h3 class="text-sm font-black text-slate-950 uppercase tracking-widest dark:text-white">Notificações</h3>
                <p class="text-[11px] text-slate-500 dark:text-gray-400">Últimas atualizações do seu acervo</p>
            </div>
            <button type="button" id="notifications-close" class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition dark:bg-white/5 dark:border-white/10 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/10" aria-label="Fechar">
                <i class="ph ph-x text-sm"></i>
            </button>
        </div>
        <div class="p-4 overflow-y-auto flex-1 space-y-3">
            @if($unreads->isEmpty() && $reads->isEmpty())
                <div class="text-center py-6 text-slate-500 text-sm dark:text-gray-400">Sem notificações por enquanto.</div>
            @endif

            @foreach($unreads as $n)
                <div class="notification-unread p-3 rounded-md bg-blue-50 border border-blue-100 dark:bg-white/5 dark:border-white/10">
                    <div class="flex items-start justify-between">
                        <div class="text-sm text-slate-900 dark:text-white">{!! $n->data['message'] ?? ($n->data['title'] ?? 'Notificação') !!}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach

            @foreach($reads as $n)
                <div class="notification-read p-3 rounded-md bg-slate-50 border border-slate-200 text-slate-500 dark:bg-transparent dark:border-white/5 dark:text-slate-400">
                    <div class="flex items-start justify-between">
                        <div class="text-sm">{!! $n->data['message'] ?? ($n->data['title'] ?? 'Notificação') !!}</div>
                        <div class="text-xs">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="space-y-2 p-4 border-t border-slate-200 dark:border-white/10">
            <a href="{{ route('notifications.index') }}" class="w-full inline-flex items-center justify-center gap-2 h-10 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 transition text-[11px] font-bold uppercase tracking-widest dark:bg-blue-500/10 dark:border-blue-500/30 dark:text-blue-300 dark:hover:bg-blue-500/20">
                Ver central
            </a>
            <button id="mark-all-read" class="w-full inline-flex items-center justify-center gap-2 h-10 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition text-[11px] font-bold uppercase tracking-widest dark:bg-white/5 dark:border-white/10 dark:text-gray-200 dark:hover:text-white dark:hover:bg-white/10">
                Marcar todas como lidas
            </button>
        </div>
    </aside>
    @endif

    {{-- ══ SCRIPTS ══ --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .ts-dropdown {
            z-index: 1000 !important;
        }
        .ts-wrapper .ts-control {
            min-height: 44px !important;
            border-radius: 6px !important;
            border-color: rgb(226 232 240) !important;
            background: #ffffff !important;
            color: rgb(30 41 59) !important;
            box-shadow: none !important;
            padding: 8px 12px !important;
        }
        .ts-wrapper.focus .ts-control {
            border-color: #1E3A8A !important;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, .18) !important;
        }
        .ts-wrapper .ts-control input {
            color: rgb(30 41 59) !important;
        }
        .ts-dropdown {
            border-color: rgb(226 232 240) !important;
            background: #ffffff !important;
            color: rgb(30 41 59) !important;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .12) !important;
        }
        .ts-dropdown .option {
            color: rgb(51 65 85) !important;
            padding: 9px 12px !important;
        }
        .ts-dropdown .option.active,
        .ts-dropdown .option:hover {
            background: rgb(239 246 255) !important;
            color: #1E3A8A !important;
        }
        .dark .ts-wrapper .ts-control {
            border-color: rgba(255,255,255,.10) !important;
            background: #080d14 !important;
            color: rgb(226 232 240) !important;
        }
        .dark .ts-wrapper .ts-control input {
            color: rgb(226 232 240) !important;
        }
        .dark .ts-dropdown {
            border-color: rgba(255,255,255,.10) !important;
            background: #080d14 !important;
            color: rgb(226 232 240) !important;
            box-shadow: 0 24px 48px rgba(0,0,0,.45) !important;
        }
        .dark .ts-dropdown .option {
            color: rgb(203 213 225) !important;
        }
        .dark .ts-dropdown .option.active,
        .dark .ts-dropdown .option:hover {
            background: rgba(30, 58, 138, .36) !important;
            color: #ffffff !important;
        }
    </style>
    
    @vite('resources/js/dashboard.js')

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded',function(){
            Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:3000,timerProgressBar:true,background:'#0d1420',color:'#fff',didOpen:t=>{t.onmouseenter=Swal.stopTimer;t.onmouseleave=Swal.resumeTimer;}}).fire({icon:'success',title:"{{ session('success') }}"});
        });
    </script>
    @endif

</x-app-layout>
