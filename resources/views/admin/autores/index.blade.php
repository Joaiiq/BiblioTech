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
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Autores</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Curadoria, catálogo e vínculos do acervo</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('autores.create') }}" class="inline-flex h-10 items-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                    <i class="ph ph-plus"></i>
                    Novo autor
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
                    <pattern id="autores-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#autores-dots)"/>
            </svg>
            <i class="ph ph-pen-nib absolute left-[7%] top-[13%] text-[42px] text-amber-500/25 dark:text-amber-300/10"></i>
            <i class="ph ph-books absolute right-[12%] top-[23%] text-[46px] text-blue-800/10 dark:text-blue-300/10"></i>
            <i class="ph ph-scroll absolute right-[20%] bottom-[17%] text-[44px] text-emerald-500/15 dark:text-emerald-300/10"></i>
        </div>

        <main class="relative z-10 mx-auto max-w-7xl space-y-6">
            @if(session('sucesso'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                    {{ session('sucesso') }}
                </div>
            @endif

            @if(session('erro'))
                <div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800 shadow-sm dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                    {{ session('erro') }}
                </div>
            @endif

            <section class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-md border border-blue-200 bg-white/95 p-4 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">Autores</p>
                        <i class="ph ph-users-three text-xl text-blue-600 dark:text-blue-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $metricas['total_autores'] }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">nomes cadastrados</p>
                </div>

                <div class="rounded-md border border-emerald-200 bg-white/95 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Com acervo</p>
                        <i class="ph ph-book-open text-xl text-emerald-600 dark:text-emerald-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $metricas['com_livros'] }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">autores com livros</p>
                </div>

                <div class="rounded-md border border-amber-200 bg-white/95 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-amber-700 dark:text-amber-300">Sem vínculo</p>
                        <i class="ph ph-warning text-xl text-amber-600 dark:text-amber-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $metricas['sem_livros'] }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">precisam de livro</p>
                </div>

                <div class="rounded-md border border-indigo-200 bg-white/95 p-4 shadow-sm dark:border-indigo-500/30 dark:bg-indigo-500/10">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-700 dark:text-indigo-300">Vínculos</p>
                        <i class="ph ph-link-simple text-xl text-indigo-600 dark:text-indigo-300"></i>
                    </div>
                    <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $metricas['total_livros_vinculados'] }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">livros conectados</p>
                </div>
            </section>

            <section class="rounded-md border border-slate-200 bg-white/95 p-4 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                <form method="GET" class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_220px_180px_auto_auto] lg:items-end">
                    <div>
                        <label for="busca" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Busca</label>
                        <div class="relative">
                            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input id="busca" name="busca" value="{{ $busca }}" placeholder="Nome, nacionalidade ou biografia" class="h-11 w-full rounded-md border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200">
                        </div>
                    </div>

                    <div>
                        <label for="nacionalidade" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Nacionalidade</label>
                        <select id="nacionalidade" name="nacionalidade" class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200">
                            <option value="todas" @selected($nacionalidade === 'todas')>Todas</option>
                            @foreach($nacionalidades as $item)
                                <option value="{{ $item }}" @selected($nacionalidade === $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="ordem" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Ordenar</label>
                        <select id="ordem" name="ordem" class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200">
                            <option value="nome" @selected($ordem === 'nome')>Nome</option>
                            <option value="mais_livros" @selected($ordem === 'mais_livros')>Mais livros</option>
                            <option value="recentes" @selected($ordem === 'recentes')>Mais recentes</option>
                        </select>
                    </div>

                    <button class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                        <i class="ph ph-funnel"></i>
                        Filtrar
                    </button>
                    <a href="{{ route('autores.index') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                        <i class="ph ph-x"></i>
                        Limpar
                    </a>
                </form>
            </section>

            <section class="overflow-hidden rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Catálogo</p>
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Autores encontrados</h2>
                    </div>
                    <span class="rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-black text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">{{ $autores->total() }}</span>
                </div>

                <div class="overflow-x-auto overscroll-x-contain pb-2">
                    <table class="min-w-[980px] w-full text-left">
                        <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:bg-[#080d14] dark:text-slate-400">
                            <tr>
                                <th class="px-5 py-3">Autor</th>
                                <th class="px-5 py-3">Perfil</th>
                                <th class="px-5 py-3">Acervo</th>
                                <th class="px-5 py-3">Cadastro</th>
                                <th class="px-5 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-white/10">
                            @forelse($autores as $autor)
                                <tr class="align-top transition hover:bg-slate-50/80 dark:hover:bg-white/[0.03]">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-md border border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-white/5">
                                                @if($autor->foto)
                                                    <img src="{{ asset('storage/' . $autor->foto) }}" alt="{{ $autor->nome }}" class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center">
                                                        <i class="ph ph-user text-2xl text-slate-400 dark:text-slate-500"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-black text-slate-950 dark:text-white">{{ $autor->nome }}</p>
                                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $autor->nacionalidade ?: 'Nacionalidade não informada' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="max-w-md px-5 py-4">
                                        <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">
                                            {{ $autor->biografia ? \Illuminate\Support\Str::limit($autor->biografia, 150) : 'Biografia ainda não cadastrada.' }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($autor->livros_count > 0)
                                            <span class="inline-flex rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                                                {{ $autor->livros_count }} livro{{ $autor->livros_count === 1 ? '' : 's' }}
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                                                Sem livros
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $autor->created_at?->format('d/m/Y') ?? '--' }}</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $autor->data_nascimento ? 'Nasc. ' . $autor->data_nascimento->format('d/m/Y') : 'Nascimento não informado' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('autores.show', $autor->id) }}" class="inline-flex h-9 items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-3 text-[10px] font-black uppercase tracking-widest text-blue-700 transition hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20">
                                                <i class="ph ph-eye"></i>
                                                Ver
                                            </a>
                                            <a href="{{ route('autores.edit', $autor->id) }}" class="inline-flex h-9 items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 text-[10px] font-black uppercase tracking-widest text-amber-700 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20">
                                                <i class="ph ph-pencil"></i>
                                                Editar
                                            </a>
                                            <form method="POST" action="{{ route('autores.destroy', $autor->id) }}" data-confirm="delete" data-title="Excluir autor?" data-text="{{ $autor->livros_count > 0 ? 'Este autor possui livros vinculados. Confira o acervo antes de remover.' : 'Esta ação não pode ser desfeita.' }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 text-[10px] font-black uppercase tracking-widest text-red-700 transition hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20">
                                                    <i class="ph ph-trash"></i>
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-14 text-center">
                                        <i class="ph ph-pen-nib mb-3 block text-5xl text-slate-300 dark:text-slate-600"></i>
                                        <p class="font-black text-slate-700 dark:text-slate-200">Nenhum autor encontrado</p>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ajuste os filtros ou cadastre o primeiro autor do acervo.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-5 py-4 dark:border-white/10">
                    {{ $autores->links() }}
                </div>
            </section>
        </main>
    </div>
</x-app-layout>
