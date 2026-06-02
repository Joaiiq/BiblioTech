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
                    <h1 class="text-lg font-black text-slate-900 dark:text-white">Painel Categorias</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Organização do acervo</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('livros.index', ['acervo' => 1]) }}" class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-200 bg-white px-3 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-arrow-left"></i>
                    Voltar
                </a>
                <button type="button" @click="dark = !dark" class="w-9 h-9 rounded-md bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition">
                    <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="-mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 min-h-screen bg-slate-100 dark:bg-[#0f172a]">
        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-[320px_minmax(0,1fr)] gap-5">
            <section class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-lg p-5 h-fit">
                <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Nova categoria</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Categorias cadastradas aparecem no formulário de livros.</p>

                @if(session('sucesso'))
                    <div class="mt-4 rounded-md border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                        {{ session('sucesso') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mt-4 rounded-md border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('categorias.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="nome" class="block text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-black mb-1">Nome</label>
                        <input id="nome" name="nome" value="{{ old('nome') }}" required class="w-full rounded-md border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label for="descricao" class="block text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-black mb-1">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="4" class="w-full rounded-md border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-sm">{{ old('descricao') }}</textarea>
                    </div>
                    <button class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-md bg-[#1E3A8A] text-white text-xs font-black uppercase tracking-widest hover:bg-blue-800 transition">
                        <i class="ph ph-plus"></i>
                        Cadastrar
                    </button>
                </form>
            </section>

            <section class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-lg overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-[#1e293b]">
                    <p class="text-[11px] uppercase tracking-[.16em] text-slate-500 dark:text-slate-400 font-black">Categorias cadastradas</p>
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ $categorias->total() }} categoria{{ $categorias->total() === 1 ? '' : 's' }}</p>
                </div>
                <div class="overflow-x-auto overscroll-x-contain pb-2">
                    <table class="min-w-[560px] w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-4 py-3">Categoria</th>
                                <th class="text-left px-4 py-3">Descrição</th>
                                <th class="text-left px-4 py-3">Livros</th>
                                <th class="text-right px-4 py-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($categorias as $categoria)
                                <tr>
                                    <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ $categoria->nome }}</td>
                                    <td class="max-w-[220px] truncate px-4 py-3 text-slate-600 dark:text-slate-300">{{ $categoria->descricao ?: 'Sem descrição' }}</td>
                                    <td class="px-4 py-3 text-amber-700 dark:text-amber-300 font-black">{{ $categoria->livros_count }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('categorias.edit', $categoria) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-300 hover:bg-amber-500/20 text-[11px] font-bold uppercase tracking-widest transition">
                                            <i class="ph ph-pencil-simple"></i>
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">Nenhuma categoria cadastrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($categorias->hasPages())
                    <div class="border-t border-slate-200 px-4 py-3 dark:border-[#1e293b]">
                        {{ $categorias->onEachSide(1)->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
