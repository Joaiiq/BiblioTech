<x-app-layout>
    @php
        $nascimento = $membro->data_nascimento ? \Carbon\Carbon::parse($membro->data_nascimento)->format('d/m/Y') : 'Não informado';
        $tipo = 'Membro';
        $statusOk = $atrasados === 0 && (float) $multasPendentes <= 0;
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
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-600 dark:text-amber-400">Identificação</p>
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Carteirinha digital</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Dados de acesso do membro ao acervo</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('membros.biblioteca') }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-arrow-left"></i>
                    Minha biblioteca
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
                    <pattern id="card-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#card-dots)"/>
            </svg>
            <i class="ph ph-identification-card absolute left-[7%] top-[15%] text-[46px] text-amber-500/20 dark:text-amber-300/10"></i>
            <i class="ph ph-books absolute right-[9%] top-[20%] text-[40px] text-blue-800/10 dark:text-blue-300/10"></i>
            <i class="ph ph-seal-check absolute right-[20%] bottom-[16%] text-[44px] text-amber-500/15 dark:text-amber-300/10"></i>
        </div>

        <main class="relative z-10 mx-auto max-w-6xl space-y-6">
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                <div class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0d1420]">
                    <div class="bg-gradient-to-r from-[#1E3A8A] via-blue-800 to-slate-950 p-5 text-white">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.22em] text-amber-300">BiblioTech</p>
                                <h2 class="mt-2 font-serif text-3xl font-black leading-tight">{{ $membro->nome }}</h2>
                                <p class="mt-1 text-sm text-blue-100">{{ $membro->email }}</p>
                            </div>
                            <div class="flex h-24 w-24 items-center justify-center rounded-md border border-white/20 bg-white/10 p-1">
                                <x-user-avatar :user="$membro" :name="$membro->nome" size="h-full w-full" text="text-2xl" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                        <div class="rounded-md border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Número da carteirinha</p>
                            <p class="mt-2 font-mono text-2xl font-black text-amber-900 dark:text-amber-200">{{ $membro->numero_carteirinha ?? 'Sem número' }}</p>
                        </div>
                        <div class="rounded-md border {{ $statusOk ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10' : 'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' }} p-4">
                            <p class="text-[10px] font-black uppercase tracking-[.18em] {{ $statusOk ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-700 dark:text-red-300' }}">Situação</p>
                            <p class="mt-2 text-2xl font-black {{ $statusOk ? 'text-emerald-800 dark:text-emerald-200' : 'text-red-800 dark:text-red-200' }}">{{ $statusOk ? 'Regular' : 'Pendência' }}</p>
                        </div>

                        <div class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">CPF</p>
                            <p class="mt-2 text-sm font-bold text-slate-950 dark:text-white">{{ $membro->cpf ?? 'Não informado' }}</p>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Telefone</p>
                            <p class="mt-2 text-sm font-bold text-slate-950 dark:text-white">{{ $membro->telefone ?? 'Não informado' }}</p>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Nascimento</p>
                            <p class="mt-2 text-sm font-bold text-slate-950 dark:text-white">{{ $nascimento }}</p>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Tipo de membro</p>
                            <p class="mt-2 text-sm font-bold text-slate-950 dark:text-white">{{ $tipo }}</p>
                        </div>
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Ações</p>
                        <div class="mt-4 space-y-2">
                            <a href="{{ route('membros.carteirinha.pdf') }}" target="_blank" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                <i class="ph ph-file-pdf"></i>
                                Abrir PDF
                            </a>
                            <a href="{{ route('emprestimos.historico') }}" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                <i class="ph ph-clock-countdown"></i>
                                Ver empréstimos
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-md border border-blue-200 bg-blue-50 p-3 dark:border-blue-500/20 dark:bg-blue-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-blue-700 dark:text-blue-300">Em uso</p>
                            <p class="mt-1 text-2xl font-black text-blue-800 dark:text-blue-300">{{ $ativos }}</p>
                        </div>
                        <div class="rounded-md border border-red-200 bg-red-50 p-3 dark:border-red-500/20 dark:bg-red-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-red-600 dark:text-red-300">Atrasos</p>
                            <p class="mt-1 text-2xl font-black text-red-700 dark:text-red-300">{{ $atrasados }}</p>
                        </div>
                        <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                            <p class="text-[10px] uppercase tracking-widest text-amber-700 dark:text-amber-300">Reservas</p>
                            <p class="mt-1 text-2xl font-black text-amber-800 dark:text-amber-300">{{ $reservasAtivas }}</p>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-[#0d1420]">
                            <p class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400">Multas</p>
                            <p class="mt-1 text-2xl font-black text-slate-950 dark:text-white">R$ {{ number_format($multasPendentes, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </aside>
            </section>
        </main>
    </div>
</x-app-layout>
