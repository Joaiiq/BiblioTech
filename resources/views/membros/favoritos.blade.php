<x-app-layout>
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
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-600 dark:text-amber-400">Quero ler</p>
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Meus favoritos</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Livros salvos para acompanhar depois</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-arrow-left"></i>
                    Voltar ao painel
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
                    <pattern id="favorites-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#favorites-dots)"/>
            </svg>
            <i class="ph ph-heart absolute left-[7%] top-[14%] text-[36px] text-amber-500/20 dark:text-amber-300/10"></i>
            <i class="ph ph-book-bookmark absolute right-[9%] top-[21%] text-[42px] text-blue-800/10 dark:text-blue-300/10"></i>
            <i class="ph ph-books absolute right-[18%] bottom-[18%] text-[46px] text-amber-500/15 dark:text-amber-300/10"></i>
        </div>

        <main class="relative z-10 mx-auto max-w-7xl space-y-6">
            <section class="overflow-hidden rounded-md border border-amber-200 bg-white/95 shadow-sm dark:border-amber-500/20 dark:bg-[#0d1420]/95">
                <div class="grid grid-cols-1 gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_280px]">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-[.16em] text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                            <i class="ph ph-heart"></i>
                            Lista pessoal
                        </span>
                        <h2 class="mt-3 max-w-3xl font-serif text-3xl font-black leading-tight text-slate-950 dark:text-white md:text-4xl">
                            Sua prateleira de interesse, sem perder livro bom no caminho.
                        </h2>
                        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            Favoritar ajuda você a separar livros para depois. Quando quiser pegar emprestado, abra a obra e confira estoque, fila e bloqueios.
                        </p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                        <p class="text-[10px] font-black uppercase tracking-[.16em] text-slate-500 dark:text-slate-400">Salvos agora</p>
                        <p class="mt-2 text-4xl font-black text-slate-950 dark:text-white">{{ $favoritos->count() }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">titulo{{ $favoritos->count() === 1 ? '' : 's' }} na lista Quero ler</p>
                    </div>
                </div>
            </section>

            @if($favoritos->isEmpty())
                <section class="rounded-md border border-slate-200 bg-white/95 p-8 text-center shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-md border border-amber-200 bg-amber-50 text-2xl text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                        <i class="ph ph-heart"></i>
                    </div>
                    <h2 class="mt-4 font-serif text-2xl font-black text-slate-950 dark:text-white">Nenhum favorito ainda</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500 dark:text-slate-400">
                        Abra um livro e use o botao Quero ler para montar sua lista.
                    </p>
                    <a href="{{ route('dashboard') }}#acervo-section" class="mt-5 inline-flex h-11 items-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                        <i class="ph ph-books"></i>
                        Explorar acervo
                    </a>
                </section>
            @else
                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($favoritos as $livro)
                        <article class="group overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-900/5 dark:border-white/10 dark:bg-[#0d1420] dark:hover:border-amber-500/40">
                            <a href="{{ route('livros.show', $livro->id) }}" class="block">
                                <div class="relative aspect-[3/4] bg-slate-100 dark:bg-white/10">
                                    @if($livro->capa)
                                        <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    @else
                                        <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-blue-100 to-amber-50 text-slate-400 dark:from-blue-950/40 dark:to-amber-950/20">
                                            <i class="ph ph-book-open-text mb-3 text-5xl"></i>
                                            <span class="text-xs font-bold uppercase tracking-widest">Sem capa</span>
                                        </div>
                                    @endif
                                    <span class="absolute left-3 top-3 rounded-md bg-[#F59E0B] px-2 py-1 text-[10px] font-black uppercase tracking-widest text-slate-950">
                                        Quero ler
                                    </span>
                                </div>
                            </a>

                            <div class="space-y-4 p-4">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[.16em] text-blue-700 dark:text-blue-300">{{ $livro->categoria ?? 'Acervo' }}</p>
                                    <h3 class="mt-1 line-clamp-2 min-h-[2.5rem] text-base font-black leading-tight text-slate-950 dark:text-white">
                                        {{ $livro->titulo }}
                                    </h3>
                                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                        {{ $livro->autor->nome ?? 'Autor não informado' }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div class="rounded-md border border-slate-200 bg-slate-50 p-2 dark:border-white/10 dark:bg-white/[.03]">
                                        <p class="text-[9px] uppercase tracking-widest text-slate-500 dark:text-slate-500">Estoque</p>
                                        <p class="mt-1 text-sm font-black text-slate-950 dark:text-white">{{ (int) $livro->quantidade }}</p>
                                    </div>
                                    <div class="rounded-md border border-slate-200 bg-slate-50 p-2 dark:border-white/10 dark:bg-white/[.03]">
                                        <p class="text-[9px] uppercase tracking-widest text-slate-500 dark:text-slate-500">Salvo em</p>
                                        <p class="mt-1 truncate text-sm font-black text-slate-950 dark:text-white">
                                            {{ optional($livro->pivot->created_at)->format('d/m') ?? '--' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <a href="{{ route('livros.show', $livro->id) }}" class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                        <i class="ph ph-eye"></i>
                                        Ver
                                    </a>
                                    <form action="{{ route('livros.favorito.toggle', $livro) }}" method="POST" data-confirm="delete" data-title="Remover favorito?" data-text="Este livro vai sair da sua lista Quero ler.">
                                        @csrf
                                        <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-700 transition hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20" aria-label="Remover favorito">
                                            <i class="ph ph-heart-break"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
            @endif
        </main>
    </div>
</x-app-layout>
