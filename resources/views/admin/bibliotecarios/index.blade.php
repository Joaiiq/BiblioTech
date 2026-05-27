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
                    <h1 class="text-lg font-black text-slate-900 dark:text-white">Painel Bibliotecários</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Equipe administrativa do sistema</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('bibliotecarios.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#1E3A8A] text-white text-[11px] font-black uppercase tracking-widest hover:bg-blue-700 transition">
                    <i class="ph ph-user-plus"></i>
                    Novo
                </a>
                <button type="button" @click="dark = !dark" class="w-9 h-9 rounded-md bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition">
                    <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="-mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 min-h-screen bg-slate-100 dark:bg-[#0f172a]">
        <div class="max-w-6xl mx-auto space-y-5">
            @if(session('sucesso'))
                <div class="rounded-md border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                    {{ session('sucesso') }}
                </div>
            @endif
            @if(session('erro'))
                <div class="rounded-md border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-700 dark:text-red-300">
                    {{ session('erro') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-lg p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-black">Equipe</p>
                    <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $bibliotecarios->count() }}</p>
                </div>
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-lg p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-black">Gerentes</p>
                    <p class="mt-2 text-2xl font-black text-amber-700 dark:text-amber-300">{{ $bibliotecarios->where('tipo_usuario', 'gerente')->count() }}</p>
                </div>
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-lg p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-black">Bibliotecários</p>
                    <p class="mt-2 text-2xl font-black text-blue-700 dark:text-blue-400">{{ $bibliotecarios->where('tipo_usuario', 'bibliotecario')->count() }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-lg overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-[#1e293b] flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Usuários administrativos</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Gerentes e bibliotecários com acesso ao painel.</p>
                    </div>
                </div>

                <div class="overflow-x-auto overscroll-x-contain pb-2">
                    <table class="min-w-[760px] w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">Nome</th>
                                <th class="text-left px-5 py-3">E-mail</th>
                                <th class="text-left px-5 py-3">Cargo</th>
                                <th class="text-left px-5 py-3">Cadastro</th>
                                <th class="text-right px-5 py-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($bibliotecarios as $bibliotecario)
                                <tr class="hover:bg-slate-50 dark:hover:bg-white/[.03]">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <x-user-avatar :user="$bibliotecario" text="text-xs" />
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $bibliotecario->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $bibliotecario->email }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider border {{ $bibliotecario->tipo_usuario === 'gerente' ? 'bg-amber-100 text-amber-700 border-amber-300 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/30' : 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-500/10 dark:text-blue-300 dark:border-blue-500/30' }}">
                                            {{ $bibliotecario->tipo_usuario === 'gerente' ? 'Gerente' : 'Bibliotecário' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-500 dark:text-slate-400">{{ $bibliotecario->created_at?->format('d/m/Y') }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('bibliotecarios.edit', $bibliotecario) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-300 hover:bg-amber-500/20 text-[11px] font-bold uppercase tracking-widest transition">
                                            <i class="ph ph-pencil-simple"></i>
                                            Editar
                                        </a>
                                        @if($bibliotecario->tipo_usuario === 'bibliotecario')
                                            <form action="{{ route('bibliotecarios.destroy', $bibliotecario) }}" method="POST" class="inline-flex" data-confirm="delete" data-title="Excluir bibliotecário?" data-text="Essa ação remove o acesso administrativo deste usuário.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ml-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-300 hover:bg-red-500/20 text-[11px] font-bold uppercase tracking-widest transition">
                                                    <i class="ph ph-trash"></i>
                                                    Excluir
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
