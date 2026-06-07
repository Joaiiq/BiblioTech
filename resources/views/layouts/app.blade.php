<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false, dark: $persist(true) }" x-effect="document.documentElement.classList.toggle('dark', dark)">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BiblioTech') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('logo.svg') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Merriweather:wght@400;700;900&display=swap" rel="stylesheet">
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

        <script src="https://unpkg.com/@phosphor-icons/web"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif !important; }
            h1, h2, h3, h4, h5, h6, .font-serif, .titulo-mw { font-family: 'Merriweather', serif !important; }
            [x-cloak] { display: none !important; }
            /* Scrollbar Dark */
            ::-webkit-scrollbar { width: 8px; }
            ::-webkit-scrollbar-track { background: #0f172a; }
            ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 0; }
            ::-webkit-scrollbar-thumb:hover { background: #334155; }
            /* Menu Ativo */
            .nav-item-active {
                background: rgba(30, 58, 138, 0.4);
                border-left: 3px solid #F59E0B;
                color: white;
            }

            /* ── Header sticky com blur — vira opaco ao rolar ── */
            #app-header {
                position: sticky;
                top: 0;
                z-index: 9000;
                background: rgba(248, 250, 252, 0.0);
                border-bottom: 1px solid transparent;
                transition: background .3s ease, border-color .3s ease, backdrop-filter .3s ease;
            }
            #app-header.scrolled {
                background: rgba(248, 250, 252, 0.92);
                border-bottom-color: rgba(15, 23, 42, 0.08);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
            }
            .dark #app-header { background: rgba(8, 13, 20, 0.0); }
            .dark #app-header.scrolled {
                background: rgba(8, 13, 20, 0.92);
                border-bottom-color: rgba(255,255,255,.04);
            }
            .swal2-container { z-index: 11050 !important; }
        </style>
    </head>
    <body class="antialiased bg-slate-50 text-slate-900 dark:bg-[#0f172a] dark:text-gray-200 overflow-x-hidden">
        
        <div class="flex min-h-screen relative">

            {{-- ── Drawer flutuante ── --}}
            <div x-data="{ open: false }">
                <button @click="open = true"
                        class="fixed bottom-6 right-6 z-[10040] w-14 h-14 shrink-0 rounded-full
                               bg-[#1E3A8A] text-white shadow-2xl shadow-blue-900/40
                               hover:bg-[#162a63] hover:scale-110 active:scale-95
                               transition-all flex items-center justify-center focus:outline-none">
                    <i class="ph ph-list text-2xl leading-none"></i>
                </button>

                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-[10000] flex">

                    <div @click="open = false" class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm dark:bg-black/60"></div>

                    <aside x-show="open"
                           x-transition:enter="transition ease-out duration-200"
                           x-transition:enter-start="-translate-x-full"
                           x-transition:enter-end="translate-x-0"
                           x-transition:leave="transition ease-in duration-150"
                           x-transition:leave-start="translate-x-0"
                           x-transition:leave-end="-translate-x-full"
                           class="relative z-[10010] flex h-full w-64 flex-col border-r border-slate-200 bg-white shadow-2xl dark:border-white/5 dark:bg-[#080d14]">

                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-white/5">
                            <div class="flex flex-col items-center justify-center gap-1">
                                <i class="ph ph-library text-[#1E3A8A] text-3xl dark:text-blue-400"></i>
                                <div class="text-[10px] font-black tracking-tighter text-center leading-tight">
                                    <span class="text-[#1E3A8A] dark:text-white">BIBLIO</span><br>
                                    <span class="text-[#F59E0B]">TECH</span>
                                </div>
                            </div>
                            <button @click="open = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-950 hover:bg-slate-100 transition dark:text-gray-500 dark:hover:text-white dark:hover:bg-white/5">
                                <i class="ph ph-x"></i>
                            </button>
                        </div>

                        @php
                            $webUser = auth()->guard('web')->user();
                            $memberUser = auth()->guard('membro')->user();
                            $drawerUser = $webUser ?: $memberUser;
                            $isDrawerMember = ! $webUser && (bool) $memberUser;
                            $isDrawerAdmin = $webUser && in_array($webUser->tipo_usuario ?? null, ['gerente', 'bibliotecario'], true);
                            $isDrawerManager = $webUser && ($webUser->tipo_usuario ?? null) === 'gerente';
                        @endphp

                        <nav class="flex flex-col gap-0.5 p-3 flex-1 overflow-y-auto">
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                                      {{ request()->routeIs('dashboard') ? 'text-[#1E3A8A] bg-blue-50 border border-blue-200 dark:text-white dark:bg-[#1E3A8A]/40 dark:border-[#1E3A8A]/50' : 'text-slate-600 hover:text-slate-950 hover:bg-slate-100 border border-transparent dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/5' }}">
                                <i class="ph-fill ph-squares-four text-blue-400 text-base shrink-0"></i>
                                Acervo
                            </a>

                            @if($drawerUser)
                                @if($isDrawerMember)
                                <div class="my-2 pt-2 border-t border-slate-200 dark:border-white/5">
                                    <p class="px-3 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500 mb-1 dark:text-slate-600">Leitor</p>
                                </div>
                                <a href="{{ route('membros.biblioteca') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('membros.biblioteca') ? 'text-blue-800 bg-blue-50 border border-blue-200 dark:text-white dark:bg-blue-900/30 dark:border-blue-400/40' : 'text-slate-600 hover:text-blue-700 hover:bg-blue-50 dark:text-gray-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/10' }}">
                                    <i class="ph ph-books text-blue-500 text-base shrink-0"></i>
                                    Minha biblioteca
                                </a>
                                <a href="{{ route('membros.situacao') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('membros.situacao') ? 'text-emerald-800 bg-emerald-50 border border-emerald-200 dark:text-white dark:bg-emerald-500/20 dark:border-emerald-400/40' : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 dark:text-gray-400 dark:hover:text-emerald-300 dark:hover:bg-emerald-900/10' }}">
                                    <i class="ph ph-clipboard-text text-emerald-500 text-base shrink-0"></i>
                                    Minha situação
                                </a>
                                <a href="{{ route('emprestimos.historico') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('emprestimos.historico') ? 'text-amber-800 bg-amber-50 border border-amber-200 dark:text-white dark:bg-[#F59E0B]/20 dark:border-[#F59E0B]/40' : 'text-slate-600 hover:text-slate-950 hover:bg-slate-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/5' }}">
                                    <i class="ph ph-clock-countdown text-amber-500/70 text-base shrink-0"></i>
                                    Meus Empréstimos
                                </a>
                                <a href="{{ route('favoritos.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('favoritos.index') ? 'text-amber-800 bg-amber-50 border border-amber-200 dark:text-white dark:bg-amber-500/20 dark:border-amber-400/40' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50 dark:text-gray-400 dark:hover:text-amber-300 dark:hover:bg-amber-900/10' }}">
                                    <i class="ph ph-heart text-amber-500 text-base shrink-0"></i>
                                    Favoritos
                                </a>
                                <a href="{{ route('membros.carteirinha') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('membros.carteirinha') ? 'text-emerald-800 bg-emerald-50 border border-emerald-200 dark:text-white dark:bg-emerald-500/20 dark:border-emerald-400/40' : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 dark:text-gray-400 dark:hover:text-emerald-300 dark:hover:bg-emerald-900/10' }}">
                                    <i class="ph ph-identification-card text-emerald-500 text-base shrink-0"></i>
                                    Carteirinha
                                </a>
                                <a href="{{ route('notifications.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('notifications.index') ? 'text-blue-800 bg-blue-50 border border-blue-200 dark:text-white dark:bg-blue-900/30 dark:border-blue-400/40' : 'text-slate-600 hover:text-blue-700 hover:bg-blue-50 dark:text-gray-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/10' }}">
                                    <i class="ph ph-bell text-blue-500 text-base shrink-0"></i>
                                    Notificações
                                </a>
                                @endif

                                <div class="my-2 pt-2 border-t border-slate-200 dark:border-white/5">
                                    <p class="px-3 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500 mb-1 dark:text-slate-600">Conta</p>
                                </div>
                                <a href="{{ route('profile.edit') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('profile.edit') ? 'text-blue-800 bg-blue-50 border border-blue-200 dark:text-white dark:bg-blue-900/30 dark:border-blue-400/40' : 'text-slate-600 hover:text-slate-950 hover:bg-slate-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/5' }}">
                                    <i class="ph ph-user text-slate-500 text-base shrink-0"></i>
                                    Meu Perfil
                                </a>

                                @if($isDrawerAdmin)
                                <div class="my-2 pt-2 border-t border-slate-200 dark:border-white/5">
                                    <p class="px-3 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500 mb-1 dark:text-slate-600">Administração</p>
                                </div>
                                <a href="{{ route('admin.operacao.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('admin.operacao.*') ? 'text-blue-700 bg-blue-50 border border-blue-200 dark:text-blue-400 dark:bg-blue-900/10 dark:border-blue-400/40' : 'text-slate-600 hover:text-blue-700 hover:bg-blue-50 dark:text-gray-400 dark:hover:text-blue-400 dark:hover:bg-blue-900/10' }}">
                                    <i class="ph ph-gauge text-blue-400 text-base shrink-0"></i>
                                    Operação
                                </a>
                                <a href="{{ route('admin.emprestimos.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('admin.emprestimos.index') ? 'text-amber-700 bg-amber-50 border border-amber-200 dark:text-amber-400 dark:bg-amber-900/10 dark:border-amber-400/40' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50 dark:text-gray-400 dark:hover:text-amber-400 dark:hover:bg-amber-900/10' }}">
                                    <i class="ph ph-handshake text-amber-500/60 text-base shrink-0"></i>
                                    Painel de Empréstimos
                                </a>
                                <a href="{{ route('livros.index', ['acervo' => 1]) }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('livros.index') ? 'text-blue-700 bg-blue-50 border border-blue-200 dark:text-blue-400 dark:bg-blue-900/10 dark:border-blue-400/40' : 'text-slate-600 hover:text-blue-700 hover:bg-blue-50 dark:text-gray-400 dark:hover:text-blue-400 dark:hover:bg-blue-900/10' }}">
                                    <i class="ph ph-books text-blue-400 text-base shrink-0"></i>
                                    Gerenciar Livros
                                </a>
                                <a href="{{ route('livros.create') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('livros.create') ? 'text-blue-700 bg-blue-50 border border-blue-200 dark:text-blue-400 dark:bg-blue-900/10 dark:border-blue-400/40' : 'text-slate-600 hover:text-blue-700 hover:bg-blue-50 dark:text-gray-400 dark:hover:text-blue-400 dark:hover:bg-blue-900/10' }}">
                                    <i class="ph ph-book-bookmark text-blue-400 text-base shrink-0"></i>
                                    Cadastrar Livro
                                </a>
                                <a href="{{ route('autores.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('autores.index', 'autores.edit') ? 'text-amber-700 bg-amber-50 border border-amber-200 dark:text-amber-400 dark:bg-amber-900/10 dark:border-amber-400/40' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50 dark:text-gray-400 dark:hover:text-amber-400 dark:hover:bg-amber-900/10' }}">
                                    <i class="ph ph-feather text-amber-500/70 text-base shrink-0"></i>
                                    Gerenciar Autores
                                </a>
                                <a href="{{ route('autores.create') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('autores.create') ? 'text-blue-700 bg-blue-50 border border-blue-200 dark:text-blue-400 dark:bg-blue-900/10 dark:border-blue-400/40' : 'text-slate-600 hover:text-blue-700 hover:bg-blue-50 dark:text-gray-400 dark:hover:text-blue-400 dark:hover:bg-blue-900/10' }}">
                                    <i class="ph ph-user-plus text-blue-400 text-base shrink-0"></i>
                                    Cadastrar Autor
                                </a>
                                <a href="{{ route('categorias.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('categorias.*') ? 'text-amber-700 bg-amber-50 border border-amber-200 dark:text-amber-400 dark:bg-amber-900/10 dark:border-amber-400/40' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50 dark:text-gray-400 dark:hover:text-amber-400 dark:hover:bg-amber-900/10' }}">
                                    <i class="ph ph-tag text-amber-500/70 text-base shrink-0"></i>
                                    Categorias
                                </a>
                                <a href="{{ route('admin.membros.perfis') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('admin.membros.perfis') ? 'text-purple-700 bg-purple-50 border border-purple-200 dark:text-purple-400 dark:bg-purple-900/10 dark:border-purple-400/40' : 'text-slate-600 hover:text-purple-700 hover:bg-purple-50 dark:text-gray-400 dark:hover:text-purple-400 dark:hover:bg-purple-900/10' }}">
                                    <i class="ph ph-users-three text-purple-400 text-base shrink-0"></i>
                                    Perfil de Membros
                                </a>
                                <a href="{{ route('admin.relatorios.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('admin.relatorios.index') ? 'text-amber-700 bg-amber-50 border border-amber-200 dark:text-amber-400 dark:bg-amber-900/10 dark:border-amber-400/40' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50 dark:text-gray-400 dark:hover:text-amber-400 dark:hover:bg-amber-900/10' }}">
                                    <i class="ph ph-chart-bar text-amber-500/70 text-base shrink-0"></i>
                                    Relatórios
                                </a>
                                <a href="{{ route('admin.multas.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('admin.multas.*') ? 'text-red-700 bg-red-50 border border-red-200 dark:text-red-400 dark:bg-red-900/10 dark:border-red-400/40' : 'text-slate-600 hover:text-red-700 hover:bg-red-50 dark:text-gray-400 dark:hover:text-red-400 dark:hover:bg-red-900/10' }}">
                                    <i class="ph ph-currency-circle-dollar text-red-500/70 text-base shrink-0"></i>
                                    Multas
                                </a>
                                <a href="{{ route('admin.auditoria.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('admin.auditoria.*') ? 'text-slate-900 bg-slate-100 border border-slate-200 dark:text-white dark:bg-white/10 dark:border-white/20' : 'text-slate-600 hover:text-slate-950 hover:bg-slate-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/5' }}">
                                    <i class="ph ph-shield-check text-slate-500 text-base shrink-0"></i>
                                    Auditoria
                                </a>
                                <a href="{{ route('membros.create') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('membros.create') ? 'text-green-700 bg-green-50 border border-green-200 dark:text-green-400 dark:bg-green-900/10 dark:border-green-400/40' : 'text-slate-600 hover:text-green-700 hover:bg-green-50 dark:text-gray-400 dark:hover:text-green-400 dark:hover:bg-green-900/10' }}">
                                    <i class="ph ph-user-check text-green-400 text-base shrink-0"></i>
                                    Cadastrar Membro
                                </a>
                                @endif
                                @if($isDrawerManager)
                                <a href="{{ route('bibliotecarios.index') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('bibliotecarios.*') ? 'text-amber-700 bg-amber-50 border border-amber-200 dark:text-amber-400 dark:bg-amber-900/10 dark:border-amber-400/40' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50 dark:text-gray-400 dark:hover:text-amber-400 dark:hover:bg-amber-900/10' }}">
                                    <i class="ph ph-user-gear text-amber-500/60 text-base shrink-0"></i>
                                    Bibliotecários
                                </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}" class="mt-8">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-bold text-red-600 hover:text-red-700 hover:bg-red-50 transition w-full dark:text-red-400 dark:hover:text-white dark:hover:bg-red-900/20">
                                        <i class="ph ph-sign-out"></i> Sair
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                                          {{ request()->routeIs('login') ? 'text-blue-800 bg-blue-50 border border-blue-200 dark:text-white dark:bg-blue-900/30 dark:border-blue-400/40' : 'text-slate-600 hover:text-slate-950 hover:bg-slate-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/5' }}">
                                    <i class="ph ph-sign-in text-slate-500 text-base shrink-0"></i>
                                    Entrar
                                </a>
                            @endif
                        </nav>
                    </aside>
                </div>
            </div>

            {{-- ── Área principal ── --}}
            <div class="flex-1 flex flex-col min-w-0 bg-slate-100 dark:bg-[#080d14]">

                {{-- Header sticky transparente — fica sobre o hero --}}
                @if (isset($header))
                    <header id="app-header" class="relative z-[9000] px-4 sm:px-6 lg:px-8 py-3">
                        <div class="max-w-7xl mx-auto min-h-[3.25rem]  flex items-start sm:items-center">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="flex-grow px-4 sm:px-6 lg:px-8 ">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @php
            $globalNotifiable = (Auth::guard('web')->check() ? Auth::guard('web')->user() : (Auth::guard('membro')->check() ? Auth::guard('membro')->user() : null));
            $globalUnreadCount = $globalNotifiable ? $globalNotifiable->unreadNotifications()->count() : 0;
        @endphp

        @if($globalNotifiable && !request()->routeIs('dashboard'))
            <div id="global-notifications-backdrop" class="fixed inset-0 z-[11020] bg-slate-950/40 opacity-0 pointer-events-none transition-opacity duration-200 dark:bg-slate-950/60" aria-hidden="true"></div>
            <aside id="global-notifications-sidebar" class="fixed top-0 right-[-420px] z-[11030] flex h-screen w-[380px] max-w-[90vw] flex-col border-l border-slate-200 bg-white shadow-2xl transition-[right] duration-200 dark:border-white/10 dark:bg-[#0d1420]" role="dialog" aria-modal="true" aria-label="Notificações">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between dark:border-white/10">
                    <div>
                        <h3 class="text-sm font-black text-slate-950 uppercase tracking-widest dark:text-white">Notificações</h3>
                        <p class="text-[11px] text-slate-500 dark:text-gray-400">Avisos do sistema</p>
                    </div>
                    <button type="button" id="global-notifications-close" class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition dark:bg-white/5 dark:border-white/10 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/10" aria-label="Fechar">
                        <i class="ph ph-x text-sm"></i>
                    </button>
                </div>
                <div class="p-4 overflow-y-auto flex-1 space-y-3">
                    @php
                        $globalUnreads = $globalNotifiable ? $globalNotifiable->unreadNotifications()->latest()->get() : collect();
                        $globalReads = $globalNotifiable ? $globalNotifiable->readNotifications()->latest()->take(30)->get() : collect();
                    @endphp

                    @if($globalUnreads->isEmpty() && $globalReads->isEmpty())
                        <div class="text-center py-6 text-slate-500 text-sm dark:text-gray-400">Sem notificações por enquanto.</div>
                    @endif

                    @foreach($globalUnreads as $n)
                        <div class="global-notification-unread p-3 rounded-md bg-blue-50 border border-blue-100 dark:bg-white/5 dark:border-white/10">
                            <div class="flex items-start justify-between">
                                <div class="text-sm text-slate-900 dark:text-white">{!! $n->data['message'] ?? ($n->data['title'] ?? 'Notificação') !!}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $n->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach

                    @foreach($globalReads as $n)
                        <div class="global-notification-read p-3 rounded-md bg-slate-50 border border-slate-200 text-slate-500 dark:bg-transparent dark:border-white/5 dark:text-slate-400">
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
                    <button id="global-mark-all-read" class="w-full inline-flex items-center justify-center gap-2 h-10 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition text-[11px] font-bold uppercase tracking-widest dark:bg-white/5 dark:border-white/10 dark:text-gray-200 dark:hover:text-white dark:hover:bg-white/10">
                        Marcar todas como lidas
                    </button>
                </div>
            </aside>
        @endif

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            /* Modal Configurado com as cores do projeto */
            const darkSwal = Swal.mixin({
                customClass: {
                    popup: 'bg-[#111827] text-white border border-gray-800 rounded-md shadow-xl',
                    confirmButton: 'px-6 py-2 mx-2 bg-red-500 hover:bg-red-600 text-white rounded-md font-bold uppercase text-xs tracking-widest transition-colors',
                    cancelButton: 'px-6 py-2 mx-2 bg-[#1e293b] border border-gray-700 hover:bg-gray-800 text-white rounded-md font-bold uppercase text-xs tracking-widest transition-colors'
                },
                buttonsStyling: false
            });

            function confirmarExclusao(event, form) {
                event.preventDefault();
                darkSwal.fire({
                    title: 'Remover Registro?',
                    text: "Esta ação não pode ser desfeita.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'EXCLUIR',
                    cancelButtonText: 'CANCELAR'
                }).then((result) => { if (result.isConfirmed) form.submit(); })
            }

            /* Header: fica opaco ao rolar */
            const appHeader = document.getElementById('app-header');
            if (appHeader) {
                const onScroll = () => {
                    appHeader.classList.toggle('scrolled', window.scrollY > 20);
                };
                window.addEventListener('scroll', onScroll, { passive: true });
            }

            const globalNotifToggle = document.getElementById('global-notifications-toggle');
            const globalNotifSidebar = document.getElementById('global-notifications-sidebar');
            const globalNotifBackdrop = document.getElementById('global-notifications-backdrop');
            const globalNotifClose = document.getElementById('global-notifications-close');
            const globalMarkAll = document.getElementById('global-mark-all-read');
            const globalBadge = document.getElementById('global-notifications-badge');

            function setGlobalNotificationsOpen(isOpen) {
                if (!globalNotifSidebar || !globalNotifBackdrop || !globalNotifToggle) return;
                globalNotifSidebar.classList.toggle('right-0', isOpen);
                globalNotifSidebar.classList.toggle('right-[-420px]', !isOpen);
                globalNotifBackdrop.classList.toggle('opacity-100', isOpen);
                globalNotifBackdrop.classList.toggle('pointer-events-auto', isOpen);
                globalNotifBackdrop.classList.toggle('pointer-events-none', !isOpen);
                document.body.classList.toggle('overflow-hidden', isOpen);
            }

            globalNotifToggle?.addEventListener('click', () => setGlobalNotificationsOpen(true));
            globalNotifClose?.addEventListener('click', () => setGlobalNotificationsOpen(false));
            globalNotifBackdrop?.addEventListener('click', () => setGlobalNotificationsOpen(false));

            if (globalBadge && typeof Swal !== 'undefined') {
                const initialCount = Number(globalBadge.textContent.replace('+', '')) || 1;
                Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:3500,timerProgressBar:true,background:'#0d1420',color:'#fff'}).fire({
                    icon:'info',
                    title: initialCount === 1 ? 'Você tem 1 aviso novo' : `Você tem ${globalBadge.textContent} avisos novos`
                });
            }

            if (globalNotifToggle) {
                let knownUnread = Number(globalBadge?.textContent.replace('+', '')) || 0;
                const notificationToast = typeof Swal !== 'undefined'
                    ? Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:3800,timerProgressBar:true,background:'#0d1420',color:'#fff'})
                    : null;

                const updateBadge = (count) => {
                    if (!globalNotifToggle) return;
                    let badge = document.getElementById('global-notifications-badge');

                    if (count <= 0) {
                        badge?.remove();
                        return;
                    }

                    if (!badge) {
                        badge = document.createElement('span');
                        badge.id = 'global-notifications-badge';
                        badge.className = 'absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-black text-white bg-red-600 rounded-full';
                        globalNotifToggle.appendChild(badge);
                    }

                    badge.textContent = count > 9 ? '9+' : count;
                };

                setInterval(() => {
                    fetch('/notifications', { headers: { 'Accept': 'application/json' } })
                        .then((response) => response.ok ? response.json() : null)
                        .then((payload) => {
                            if (!payload) return;
                            const unread = Number(payload.unread_count || 0);
                            updateBadge(unread);

                            if (unread > knownUnread && notificationToast) {
                                notificationToast.fire({
                                    icon: 'info',
                                    title: unread - knownUnread === 1 ? 'Novo aviso na sua conta' : `${unread - knownUnread} novos avisos na sua conta`,
                                });
                            }

                            knownUnread = unread;
                        })
                        .catch(() => {});
                }, 45000);
            }

            globalMarkAll?.addEventListener('click', function () {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('/notifications/mark-read', { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(() => {
                        setGlobalNotificationsOpen(false);
                        globalBadge?.remove();
                        const currentBadge = document.getElementById('global-notifications-badge');
                        currentBadge?.remove();
                        document.querySelectorAll('.global-notification-unread').forEach(el => {
                            el.className = 'global-notification-read p-3 rounded-md bg-slate-50 border border-slate-200 text-slate-500 dark:bg-transparent dark:border-white/5 dark:text-slate-400';
                        });
                        if (typeof Swal !== 'undefined') {
                            Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2500,timerProgressBar:true,background:'#0d1420',color:'#fff'}).fire({icon:'success',title:'Notificações marcadas como lidas'});
                        }
                    });
            });

            // Toasts de validação + marcação vermelha dos campos com erro
            (function () {
                if (typeof Swal === 'undefined') return;

                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    background: '#0d1420',
                    color: '#fff'
                });

                const applyFieldError = (fieldName) => {
                    const safeName = String(fieldName).replace(/"/g, '\\"');
                    const selector = `[name="${safeName}"], [name="${safeName}[]"]`;
                    const field = document.querySelector(selector);
                    if (!field) return;

                    field.setAttribute('aria-invalid', 'true');
                    field.style.borderColor = '#ef4444';
                    field.style.boxShadow = '0 0 0 1px #ef4444';
                };

                const errors = {!! json_encode($errors->getMessages()) !!};
                Object.entries(errors).forEach(([field, messages]) => {
                    applyFieldError(field);
                    (messages || []).forEach((message) => toast.fire({ icon: 'error', title: message }));
                });

                @if(session('sucesso'))
                    toast.fire({ icon: 'success', title: {!! json_encode(session('sucesso')) !!} });
                @endif
                @if(session('error'))
                    toast.fire({ icon: 'error', title: {!! json_encode(session('error')) !!} });
                @endif
                @if(session('erro'))
                    toast.fire({ icon: 'error', title: {!! json_encode(session('erro')) !!} });
                @endif
                @if(session('status'))
                    toast.fire({ icon: 'info', title: {!! json_encode(session('status')) !!} });
                @endif
            })();

            document.querySelectorAll('form[data-confirm]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    const isDelete = form.dataset.confirm === 'delete';
                    const lockForm = () => {
                        const submit = form.querySelector('[type="submit"]');
                        if (!submit) return;
                        submit.disabled = true;
                        submit.classList.add('opacity-70', 'cursor-wait');
                        submit.dataset.originalText = submit.innerHTML;
                        submit.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Processando';
                    };

                    darkSwal.fire({
                        title: form.dataset.title || 'Confirmar ação?',
                        text: form.dataset.text || 'Deseja continuar?',
                        icon: isDelete ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonText: isDelete ? 'EXCLUIR' : 'CONFIRMAR',
                        cancelButtonText: 'CANCELAR',
                        customClass: {
                            popup: 'bg-[#111827] text-white border border-gray-800 rounded-md shadow-xl',
                            confirmButton: `px-6 py-2 mx-2 ${isDelete ? 'bg-red-500 hover:bg-red-600' : 'bg-[#1E3A8A] hover:bg-blue-800'} text-white rounded-md font-bold uppercase text-xs tracking-widest transition-colors`,
                            cancelButton: 'px-6 py-2 mx-2 bg-[#1e293b] border border-gray-700 hover:bg-gray-800 text-white rounded-md font-bold uppercase text-xs tracking-widest transition-colors'
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            lockForm();
                            form.submit();
                        }
                    });
                });
            });

            document.querySelectorAll('form:not([data-confirm])').forEach((form) => {
                form.addEventListener('submit', () => {
                    const submit = form.querySelector('[type="submit"]');
                    if (!submit) return;
                    submit.disabled = true;
                    submit.classList.add('opacity-70', 'cursor-wait');
                    const label = form.dataset.submitLabel || 'Processando';
                    submit.innerHTML = `<i class="ph ph-circle-notch animate-spin"></i> ${label}`;
                });
            });

            document.querySelectorAll('[data-avatar-zoom]').forEach((avatar) => {
                const openAvatar = () => {
                    if (typeof Swal === 'undefined') return;

                    const imageUrl = avatar.dataset.avatarZoom;
                    const imageName = avatar.dataset.avatarName || 'Foto do usuário';

                    Swal.fire({
                        title: imageName,
                        imageUrl,
                        imageAlt: imageName,
                        width: 'min(92vw, 760px)',
                        background: document.documentElement.classList.contains('dark') ? '#0d1420' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#0f172a',
                        showConfirmButton: false,
                        showCloseButton: true,
                        customClass: {
                            popup: 'rounded-md border border-slate-200 dark:border-white/10 shadow-2xl',
                            image: 'max-h-[72vh] object-contain rounded-md',
                            title: 'text-base font-black',
                        },
                    });
                };

                avatar.addEventListener('click', openAvatar);
                avatar.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openAvatar();
                    }
                });
            });

        </script>
    </body>
</html>
