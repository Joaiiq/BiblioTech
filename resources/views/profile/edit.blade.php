<x-app-layout>
    @php
        $authUser = auth()->guard('web')->user() ?: auth()->guard('membro')->user() ?: $user;
        $isMembro = !auth()->guard('web')->check() && (auth()->guard('membro')->check() || $authUser instanceof \App\Models\Membros);
        $nomePerfil = $authUser->name ?? $authUser->nome ?? 'Usuário';
        $emailPerfil = $authUser->email ?? 'sem-email@bibliotech.local';
        $fotoPerfil = $authUser->profile_photo_path ? asset('storage/' . $authUser->profile_photo_path) : null;
        $iniciaisPerfil = collect(explode(' ', trim($nomePerfil)))
            ->filter()
            ->map(fn ($parte) => strtoupper(mb_substr($parte, 0, 1)))
            ->take(2)
            ->join('') ?: 'BT';

        $emprestimosAtivosPerfil = $isMembro
            ? $authUser->emprestimos()->whereIn('status', \App\Models\Emprestimos::STATUS_EM_ANDAMENTO)->count()
            : null;
        $reservasAtivasPerfil = $isMembro
            ? $authUser->reservas()->where('status', \App\Models\Reserva::STATUS_ATIVA)->count()
            : null;
        $favoritosPerfil = $isMembro ? $authUser->favoritos()->count() : null;
        $pendenciasPerfil = $isMembro
            ? $authUser->emprestimos()
                ->whereIn('status', \App\Models\Emprestimos::STATUS_EM_ANDAMENTO)
                ->whereDate('data_devolucao_prevista', '<', today())
                ->count()
            : null;
    @endphp

    <x-slot name="header">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center gap-1 shrink-0">
                    <i class="ph ph-library text-[#1E3A8A] dark:text-blue-400 text-4xl"></i>
                    <div class="text-[11px] font-black tracking-tight text-center leading-tight">
                        <span class="text-[#1E3A8A] dark:text-blue-400">BIBLIO</span><br>
                        <span class="text-[#F59E0B]">TECH</span>
                    </div>
                </a>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">
                        {{ $isMembro ? 'Área do membro' : 'Conta administrativa' }}
                    </p>
                    <h1 class="truncate text-xl font-black text-slate-950 dark:text-white font-serif">Meu perfil</h1>
                </div>
            </div>

            <button type="button" @click="dark = !dark" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-slate-950 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">
                <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
            </button>
        </div>
    </x-slot>

    <div class="-mx-4 min-h-[calc(100vh-5rem)] bg-slate-100 px-4 py-8 dark:bg-[#0b1120] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="max-w-6xl mx-auto">
            <div class="mb-6 rounded-md border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420] sm:p-6">
                <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-md bg-[#1E3A8A] text-xl font-black text-white shadow-lg shadow-blue-900/20">
                            @if($fotoPerfil)
                                <img src="{{ $fotoPerfil }}" alt="{{ $nomePerfil }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    {{ $iniciaisPerfil }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="mb-1 inline-flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[.16em] text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                                <i class="ph ph-user-circle"></i>
                                {{ $isMembro ? 'Leitor ativo' : ucfirst($authUser->tipo_usuario ?? 'Equipe') }}
                            </p>
                            <h2 class="truncate text-2xl font-black text-slate-950 dark:text-white font-serif">{{ $nomePerfil }}</h2>
                            <p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">{{ $emailPerfil }}</p>
                        </div>
                    </div>

                    @if($isMembro)
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 md:w-[460px]">
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Ativos</p>
                                <p class="mt-1 text-lg font-black text-slate-950 dark:text-white">{{ $emprestimosAtivosPerfil }}</p>
                            </div>
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Reservas</p>
                                <p class="mt-1 text-lg font-black text-slate-950 dark:text-white">{{ $reservasAtivasPerfil }}</p>
                            </div>
                            <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                                <p class="text-[9px] font-black uppercase tracking-widest text-amber-700 dark:text-amber-300">Favoritos</p>
                                <p class="mt-1 text-lg font-black text-amber-800 dark:text-amber-300">{{ $favoritosPerfil }}</p>
                            </div>
                            <div class="rounded-md border {{ $pendenciasPerfil ? 'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' : 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10' }} p-3">
                                <p class="text-[9px] font-black uppercase tracking-widest {{ $pendenciasPerfil ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">Pendências</p>
                                <p class="mt-1 text-lg font-black {{ $pendenciasPerfil ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ $pendenciasPerfil }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                    <section class="rounded-md border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Conta</p>
                        <dl class="mt-4 space-y-4 text-sm">
                            @if($isMembro)
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-500">Carteirinha</dt>
                                    <dd class="mt-1 font-black text-blue-700 dark:text-blue-300">{{ $authUser->numero_carteirinha ?? 'Pendente' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-500">Perfil</dt>
                                    <dd class="mt-1 font-bold text-slate-950 dark:text-white">Membro</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-500">Telefone</dt>
                                    <dd class="mt-1 font-bold text-slate-950 dark:text-white">{{ $authUser->telefone ?? 'Não informado' }}</dd>
                                </div>
                            @else
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-500">Permissão</dt>
                                    <dd class="mt-1 font-bold text-slate-950 dark:text-white">{{ ucfirst($authUser->tipo_usuario ?? 'Equipe') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-500">Status</dt>
                                    <dd class="mt-1 font-bold text-emerald-700 dark:text-emerald-300">Ativo</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    @if($isMembro)
                        <section class="rounded-md border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]">
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Atalhos</p>
                            <div class="mt-4 space-y-2">
                                <a href="{{ route('membros.biblioteca') }}" class="flex h-11 items-center justify-between rounded-md border border-slate-200 bg-slate-50 px-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100 hover:text-slate-950 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                                    Biblioteca
                                    <i class="ph ph-books"></i>
                                </a>
                                <a href="{{ route('emprestimos.historico') }}" class="flex h-11 items-center justify-between rounded-md border border-slate-200 bg-slate-50 px-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100 hover:text-slate-950 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                                    Empréstimos
                                    <i class="ph ph-clock-countdown"></i>
                                </a>
                                <a href="{{ route('favoritos.index') }}" class="flex h-11 items-center justify-between rounded-md border border-amber-200 bg-amber-50 px-3 text-sm font-bold text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20">
                                    Favoritos
                                    <i class="ph ph-heart"></i>
                                </a>
                                <a href="{{ route('membros.carteirinha') }}" class="flex h-11 items-center justify-between rounded-md border border-emerald-200 bg-emerald-50 px-3 text-sm font-bold text-emerald-800 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">
                                    Carteirinha
                                    <i class="ph ph-identification-card"></i>
                                </a>
                            </div>
                        </section>
                    @endif
                </aside>

                <div class="space-y-6">
                    <section class="rounded-md border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420] sm:p-7">
                        @include('profile.partials.update-profile-information-form')
                    </section>

                    <section class="rounded-md border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420] sm:p-7">
                        @include('profile.partials.update-password-form')
                    </section>

                    <section class="rounded-md border border-red-200 bg-red-50 p-5 shadow-sm dark:border-red-500/20 dark:bg-red-500/10 sm:p-7">
                        @include('profile.partials.delete-user-form')
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
