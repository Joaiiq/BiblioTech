<nav x-data="{ open: false }" class="bg-white dark:bg-[#111827] border-b border-gray-100 dark:border-gray-800 shadow-sm font-inter relative z-40" style="font-family: 'Inter', sans-serif;">
    @php
        $authUser = Auth::guard('web')->check()
            ? Auth::guard('web')->user()
            : (Auth::guard('membro')->check() ? Auth::guard('membro')->user() : null);
        
        $ehMembro = ! Auth::guard('web')->check() && Auth::guard('membro')->check();
        $ehAdmin  = Auth::guard('web')->check() && 
                    in_array(Auth::guard('web')->user()->tipo_usuario ?? '', ['gerente', 'bibliotecario']);
        $globalNotifiable = $authUser;
        $globalUnreadCount = $globalNotifiable ? $globalNotifiable->unreadNotifications()->count() : 0;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:opacity-80 transition flex flex-col items-center justify-center gap-1">
                        <i class="ph ph-library text-[#1E3A8A] dark:text-blue-400 text-4xl"></i>
                        <div class="text-[11px] font-black tracking-tighter text-center leading-tight">
                            <span class="text-[#1E3A8A] dark:text-blue-400">BIBLIO</span><br>
                            <span class="text-[#F59E0B]">TECH</span>
                        </div>
                    </a>
                </div>
            </div>


            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
                @if($globalNotifiable && !request()->routeIs('dashboard'))
                    <button type="button" id="global-notifications-toggle" class="relative inline-flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition" aria-controls="global-notifications-sidebar" aria-label="Notificações">
                        <i class="ph ph-bell text-sm"></i>
                        @if($globalUnreadCount)
                            <span id="global-notifications-badge" class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-black text-white bg-red-600 rounded-full">{{ $globalUnreadCount > 9 ? '9+' : $globalUnreadCount }}</span>
                        @endif
                    </button>
                @endif

                {{-- Dropdown do usuário --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-black uppercase tracking-widest rounded-md text-[#1F2937] dark:text-gray-300 bg-white dark:bg-[#111827] hover:text-[#1E3A8A] dark:hover:text-blue-400 transition">
                            <x-user-avatar :user="$authUser" size="h-7 w-7" text="text-[9px]" class="mr-2" />
                            <div>{{ $authUser ? ($authUser->name ?? $authUser->nome) : 'Usuário' }}</div>
                            <svg class="ms-1 fill-current h-4 w-4" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="text-[10px] font-bold uppercase tracking-widest">
                            Meu Perfil
                        </x-dropdown-link>

                        {{-- Item exclusivo para membros --}}
                        @if($ehMembro)
                            <x-dropdown-link :href="route('dashboard')" class="text-[10px] font-bold uppercase tracking-widest">
                                Início
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('emprestimos.historico')" class="text-[10px] font-bold uppercase tracking-widest">
                                Meus Empréstimos
                            </x-dropdown-link>
                        @endif

                        {{-- Item exclusivo para admins --}}
                        @if($ehAdmin)
                            <x-dropdown-link :href="route('admin.emprestimos.index')" class="text-[10px] font-bold uppercase tracking-widest">
                                Painel de Empréstimos
                            </x-dropdown-link>
                        @endif

                        <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link
                                :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="text-[#EF4444] font-black text-[10px] uppercase tracking-widest">
                                Sair
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Botão hamburguer mobile --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 rounded-md text-gray-400 hover:text-[#F59E0B] transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menu mobile --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         @click.away="open = false"
         class="fixed inset-x-0 top-16 sm:hidden bg-white dark:bg-[#111827] border-b border-gray-200 dark:border-gray-800 shadow-2xl z-[100]"
         x-cloak>

        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-800">
            <div class="px-4 mb-3">
                <div class="font-black text-sm text-[#1F2937] dark:text-gray-200 uppercase tracking-widest">
                    {{ $authUser ? ($authUser->name ?? $authUser->nome) : 'Usuário' }}
                </div>
                <div class="font-medium text-[10px] text-gray-500 dark:text-gray-400">
                    {{ $authUser ? $authUser->email : '' }}
                </div>
            </div>

            <div class="space-y-1 pb-4">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-xs font-bold uppercase tracking-widest">
                    Meu Perfil
                </x-responsive-nav-link>

                @if($ehMembro)
                    <x-responsive-nav-link :href="route('dashboard')" class="text-xs font-bold uppercase tracking-widest">
                        Início
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('emprestimos.historico')" class="text-xs font-bold uppercase tracking-widest">
                        Meus Empréstimos
                    </x-responsive-nav-link>
                @endif

                @if($ehAdmin)
                    <x-responsive-nav-link :href="route('admin.emprestimos.index')" class="text-xs font-bold uppercase tracking-widest">
                        Painel de Empréstimos
                    </x-responsive-nav-link>
                @endif

                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                        class="text-[#EF4444] font-black text-xs uppercase tracking-widest">
                        Sair do Sistema
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
