<x-app-layout>
    @php
        $disponivel = (int) $livro->quantidade > 0;
        $anoPublicacao = $livro->data_publicacao ? \Carbon\Carbon::parse($livro->data_publicacao)->format('Y') : 'Ano não informado';
        $statusTexto = $disponivel ? 'Disponível para empréstimo' : 'Indisponível no momento';
        $statusClasse = $disponivel
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
            : 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300';
        $podeGerenciarLivro = auth()->guard('web')->check()
            && in_array(auth()->guard('web')->user()?->tipo_usuario, ['gerente', 'bibliotecario'], true);
    @endphp

    <div class="-mx-4 min-h-screen bg-gradient-to-b from-slate-100 via-blue-50 to-slate-100 px-4 py-8 dark:from-[#0f172a] dark:via-[#0f172a] dark:to-[#0b1120] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="book-show-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.08"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#book-show-dots)"/>
            </svg>
            <i class="ph ph-book-open absolute left-[6%] top-[12%] text-[42px] text-amber-500/15 dark:text-blue-300/10"></i>
            <i class="ph ph-bookmarks absolute right-[10%] top-[18%] text-[36px] text-blue-800/10 dark:text-amber-300/10"></i>
            <i class="ph ph-library absolute right-[18%] bottom-[18%] text-[46px] text-amber-500/15 dark:text-blue-300/10"></i>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('livros.index', ['acervo' => 1]) }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-arrow-left"></i>
                    Voltar ao acervo
                </a>

                @if($podeGerenciarLivro)
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('livros.edit', $livro->id) }}" class="inline-flex h-10 items-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                            <i class="ph ph-pencil-simple"></i>
                            Editar obra
                        </a>
                        <form action="{{ route('livros.destroy', $livro->id) }}" method="POST" data-confirm="delete" data-title="Excluir livro?" data-text="O livro sairá do acervo, mas o histórico de empréstimos será preservado.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-md border border-red-200 bg-red-50 px-4 text-[11px] font-black uppercase tracking-widest text-red-700 transition hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20">
                                <i class="ph ph-trash"></i>
                                Excluir livro
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <section class="overflow-hidden rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/[.06] dark:bg-[#0d1420]/95">
                <div class="grid grid-cols-1 lg:grid-cols-[340px_minmax(0,1fr)]">
                    <div class="border-b border-slate-200 bg-slate-50 p-6 dark:border-white/[.06] dark:bg-white/[.03] lg:border-b-0 lg:border-r">
                        <div class="mx-auto max-w-[280px]">
                            <div class="relative aspect-[3/4] overflow-hidden rounded-md bg-slate-100 shadow-2xl shadow-slate-950/10 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10">
                                @if($livro->capa)
                                    <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-blue-100 to-amber-50 text-slate-400 dark:from-blue-950/40 dark:to-amber-950/20">
                                        <i class="ph ph-book-open-text mb-3 text-5xl"></i>
                                        <span class="text-xs font-bold uppercase tracking-widest">Sem capa</span>
                                    </div>
                                @endif
                                @if($livro->e_bestseller)
                                    <span class="absolute left-3 top-3 rounded-md bg-[#F59E0B] px-2 py-1 text-[10px] font-black uppercase tracking-widest text-slate-950">Destaque</span>
                                @endif
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="rounded-md border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-[#0d1420]">
                                    <p class="text-[10px] uppercase tracking-widest text-slate-500">Estoque</p>
                                    <p class="mt-1 text-lg font-black text-slate-950 dark:text-white">{{ (int) $livro->quantidade }}</p>
                                </div>
                                <div class="rounded-md border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-[#0d1420]">
                                    <p class="text-[10px] uppercase tracking-widest text-slate-500">Fila</p>
                                    <p class="mt-1 text-lg font-black text-slate-950 dark:text-white">{{ $reservasAtivas->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 sm:p-8 lg:p-10">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-[10px] font-black uppercase tracking-widest {{ $statusClasse }}">
                                {{ $statusTexto }}
                            </span>
                            <span class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                                {{ $livro->categoriasTexto() }}
                            </span>
                            <span class="inline-flex items-center rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                                {{ $anoPublicacao }}
                            </span>
                        </div>

                        <h1 class="mt-4 max-w-4xl text-3xl font-black leading-tight text-slate-950 dark:text-white font-serif md:text-5xl">
                            {{ $livro->titulo }}
                        </h1>
                        <p class="mt-2 text-base text-slate-600 dark:text-slate-400">
                            por
                            @if($livro->autor)
                                <a href="{{ route('autores.show', $livro->autor->id) }}" class="font-bold text-blue-700 transition hover:text-amber-600 dark:text-blue-300 dark:hover:text-amber-300">{{ $livro->autor->nome }}</a>
                            @else
                                <span class="font-bold">Autor não informado</span>
                            @endif
                        </p>

                        @if($isMembroOperacional)
                            <form action="{{ route('livros.favorito.toggle', $livro) }}" method="POST" class="mt-5" data-confirm="favorite" data-title="{{ $isFavorito ? 'Remover dos favoritos?' : 'Adicionar aos favoritos?' }}" data-text="{{ $isFavorito ? 'Este livro sairá da sua lista Quero ler.' : 'Este livro ficará salvo na sua lista Quero ler.' }}">
                                @csrf
                                <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-md border px-4 text-[11px] font-black uppercase tracking-widest transition {{ $isFavorito ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20' : 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20' }}">
                                    <i class="ph {{ $isFavorito ? 'ph-fill ph-heart' : 'ph-heart' }}"></i>
                                    {{ $isFavorito ? 'Remover dos favoritos' : 'Quero ler' }}
                                </button>
                            </form>
                        @endif

                        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                                <p class="text-[10px] uppercase tracking-widest text-slate-500">Editora</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-900 dark:text-white">{{ $livro->editora ?: '—' }}</p>
                            </div>
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                                <p class="text-[10px] uppercase tracking-widest text-slate-500">Páginas</p>
                                <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $livro->paginas ?: '—' }}</p>
                            </div>
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                                <p class="text-[10px] uppercase tracking-widest text-slate-500">Estante</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-900 dark:text-white">{{ $livro->estante ?: '—' }}</p>
                            </div>
                            <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                                <p class="text-[10px] uppercase tracking-widest text-slate-500">Local</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-900 dark:text-white">{{ $livro->localizacao ?: '—' }}</p>
                            </div>
                        </div>

                        <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                            <div class="space-y-6">
                                <div>
                                    <h2 class="text-sm font-black uppercase tracking-[.16em] text-slate-900 dark:text-white">Sobre a obra</h2>
                                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                        {{ $livro->sinopse ?: 'Nenhuma sinopse disponível para esta obra.' }}
                                    </p>
                                </div>

                                @if($livro->preview)
                                    <div class="rounded-md border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                                        <h2 class="text-[11px] font-black uppercase tracking-[.16em] text-amber-800 dark:text-amber-300">Prévia</h2>
                                        <p class="mt-2 text-sm italic leading-relaxed text-amber-900 dark:text-amber-100">
                                            "{{ Str::limit($livro->preview, 500) }}"
                                        </p>
                                    </div>
                                @endif

                                @if($livro->preview_pdf)
                                    <div class="overflow-hidden rounded-md border border-blue-200 bg-blue-50 dark:border-blue-500/20 dark:bg-blue-500/10">
                                        <div class="flex flex-col gap-3 border-b border-blue-200 p-4 dark:border-blue-500/20 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <h2 class="text-[11px] font-black uppercase tracking-[.16em] text-blue-800 dark:text-blue-300">Prévia em páginas</h2>
                                                <p class="mt-1 text-sm text-blue-900/80 dark:text-blue-100/80">Leia algumas páginas antes de solicitar o empréstimo.</p>
                                            </div>
                                            <a href="{{ asset('storage/' . $livro->preview_pdf) }}" target="_blank" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                                <i class="ph ph-file-pdf"></i>
                                                Abrir PDF
                                            </a>
                                        </div>
                                        <iframe src="{{ asset('storage/' . $livro->preview_pdf) }}#toolbar=0&navpanes=0" title="Prévia de {{ $livro->titulo }}" class="h-[520px] w-full bg-white dark:bg-[#0d1420]"></iframe>
                                    </div>
                                @endif
                            </div>

                            <aside class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <h2 class="text-[11px] font-black uppercase tracking-[.16em] text-slate-900 dark:text-white">Empréstimo</h2>
                                    <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase tracking-widest {{ $livro->e_bestseller ? 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300' : 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300' }}">
                                        {{ $livro->e_bestseller ? 'Destaque' : 'Comum' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-md bg-white p-3 dark:bg-[#0d1420]">
                                        <p class="text-[10px] uppercase tracking-widest text-slate-500">Prazo</p>
                                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ $prazoEmprestimoDias }}d</p>
                                    </div>
                                    <div class="rounded-md bg-white p-3 dark:bg-[#0d1420]">
                                        <p class="text-[10px] uppercase tracking-widest text-slate-500">Multa</p>
                                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">R$1</p>
                                    </div>
                                    <div class="rounded-md bg-white p-3 dark:bg-[#0d1420]">
                                        <p class="text-[10px] uppercase tracking-widest text-slate-500">Limite</p>
                                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">3</p>
                                    </div>
                                </div>

                                @if($isMembroOperacional && !empty($bloqueiosEmprestimo))
                                    <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-amber-800 dark:text-amber-300">Situação do pedido</p>
                                        <ul class="mt-2 space-y-1">
                                            @foreach($bloqueiosEmprestimo as $bloqueio)
                                                <li class="flex gap-2 text-xs text-amber-900 dark:text-amber-100">
                                                    <i class="ph ph-info mt-0.5 shrink-0"></i>
                                                    <span>{{ $bloqueio }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if($reservasAtivas->isNotEmpty())
                                    <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-amber-800 dark:text-amber-300">Fila de reserva</p>
                                        <p class="mt-1 text-xs text-amber-900 dark:text-amber-100">
                                            {{ $reservasAtivas->count() }} membro{{ $reservasAtivas->count() === 1 ? '' : 's' }} aguardando.
                                        </p>
                                        @if($reservaDoMembro)
                                            <p class="mt-2 inline-flex rounded-md bg-white px-2 py-1 text-xs font-black text-amber-800 dark:bg-[#0d1420] dark:text-amber-300">
                                                Sua posição: {{ $posicaoReserva }}
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                <div class="mt-5 space-y-3">
                                    @if($isMembroOperacional)
                                        @if($livro->quantidade > 0)
                                            @if($podeSolicitarEmprestimo)
                                                <form action="{{ route('livros.alugar', $livro->id) }}" method="POST" data-confirm="loan" data-title="Solicitar empréstimo?" data-text="O prazo previsto para este livro é de {{ $prazoEmprestimoDias }} dias.">
                                                    @csrf
                                                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                                        <i class="ph ph-handshake"></i>
                                                        Solicitar empréstimo
                                                    </button>
                                                </form>
                                            @else
                                                <button disabled class="h-11 w-full rounded-md border border-slate-200 bg-slate-100 text-[11px] font-black uppercase tracking-widest text-slate-400 dark:border-white/10 dark:bg-white/5">Solicitação bloqueada</button>
                                            @endif
                                        @else
                                            @if($podeReservar)
                                                <form action="{{ route('livros.reservar', $livro->id) }}" method="POST" data-confirm="reserve" data-title="Entrar na fila?" data-text="Quando houver exemplar disponível, a biblioteca poderá atender sua reserva.">
                                                    @csrf
                                                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-[#F59E0B] px-4 text-[11px] font-black uppercase tracking-widest text-slate-950 transition hover:bg-amber-400">
                                                        <i class="ph ph-bookmark-simple"></i>
                                                        Reservar livro
                                                    </button>
                                                </form>
                                            @elseif($reservaDoMembro)
                                                <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                                                    <p class="text-center text-xs font-bold text-amber-800 dark:text-amber-300">
                                                        Você está na posição {{ $posicaoReserva }} da fila
                                                    </p>
                                                    <form action="{{ route('reservas.cancelar', $reservaDoMembro->id) }}" method="POST" class="mt-3" data-confirm="delete" data-title="Cancelar reserva?" data-text="Você sairá da fila deste livro.">
                                                        @csrf
                                                        <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md border border-amber-300 bg-white px-3 text-[10px] font-black uppercase tracking-widest text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-[#0d1420] dark:text-amber-300 dark:hover:bg-amber-500/10">
                                                            <i class="ph ph-x-circle"></i>
                                                            Cancelar reserva
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <button disabled class="h-11 w-full rounded-md border border-slate-200 bg-slate-100 text-[11px] font-black uppercase tracking-widest text-slate-400 dark:border-white/10 dark:bg-white/5">Obra indisponível</button>
                                            @endif
                                        @endif
                                    @elseif(auth()->guard('web')->check())
                                        <div class="space-y-3 rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                                            <label for="membro_id_admin" class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                                Emprestar para membro
                                            </label>
                                            <form action="{{ route('admin.emprestimos.balcao') }}" method="POST" class="space-y-3">
                                                @csrf
                                                <input type="hidden" name="livro_id" value="{{ $livro->id }}">
                                                <select id="membro_id_admin" name="membro_id" required class="block h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-white">
                                                    <option value="">Selecione o membro</option>
                                                    @foreach($membrosParaEmprestimo as $membroItem)
                                                        <option value="{{ $membroItem->id }}">
                                                            {{ $membroItem->nome }} · {{ $membroItem->numero_carteirinha ?? $membroItem->email }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                                    <i class="ph ph-handshake"></i>
                                                    Registrar empréstimo
                                                </button>
                                            </form>
                                            <a href="{{ route('livros.edit', $livro->id) }}" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                                <i class="ph ph-pencil-simple"></i>
                                                Editar obra
                                            </a>
                                        </div>
                                    @else
                                        <a href="{{ route('login') }}" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                            <i class="ph ph-sign-in"></i>
                                            Fazer login para solicitar
                                        </a>
                                    @endif
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/[.06] dark:bg-[#0d1420]/95 sm:p-6">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-950 dark:text-white font-serif">Comentários</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $totalComentarios }} comentário{{ $totalComentarios === 1 ? '' : 's' }}</p>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="ph {{ $mediaNota && $mediaNota >= $i ? 'ph-fill ph-star text-amber-400' : 'ph-star text-slate-300 dark:text-slate-700' }}"></i>
                            @endfor
                        </div>
                        <span class="font-bold">{{ $mediaNota ? number_format($mediaNota, 1, ',', '.') : '0,0' }} / 5</span>
                    </div>
                </div>

                @if($isMembroOperacional || auth()->guard('web')->check())
                    @if($comentarioExistente)
                        <div class="mb-6 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                            Você já comentou este livro. Edite seu comentário na lista abaixo.
                        </div>
                    @elseif(!($podeComentar ?? false))
                        <div class="mb-6 rounded-md border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">
                            Só é possível comentar livros que você já devolveu.
                        </div>
                    @else
                        <form action="{{ route('livros.comentarios.store', $livro->id) }}" method="POST" class="mb-8 rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]" data-confirm="comment" data-title="Publicar comentário?" data-text="Sua avaliação ficará visível nesta página.">
                            @csrf
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-[180px_minmax(0,1fr)]">
                                <div>
                                    <label for="nota" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Nota</label>
                                    <select id="nota" name="nota" class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-200">
                                        <option value="">Selecione</option>
                                        <option value="5" @selected(old('nota') == 5)>5 - Excelente</option>
                                        <option value="4" @selected(old('nota') == 4)>4 - Muito bom</option>
                                        <option value="3" @selected(old('nota') == 3)>3 - Bom</option>
                                        <option value="2" @selected(old('nota') == 2)>2 - Regular</option>
                                        <option value="1" @selected(old('nota') == 1)>1 - Ruim</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="comentario" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Comentário</label>
                                    <textarea id="comentario" name="comentario" rows="4" class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-200" placeholder="Compartilhe sua opinião...">{{ old('comentario') }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="mt-4 inline-flex h-10 items-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                <i class="ph ph-chat-circle-text"></i>
                                Enviar comentário
                            </button>
                        </form>
                    @endif
                @else
                    <div class="mb-6 rounded-md border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">
                        <a href="{{ route('login') }}" class="font-bold text-blue-700 hover:text-amber-600 dark:text-blue-300">Faça login</a> para comentar.
                    </div>
                @endif

                <div class="space-y-4">
                    @forelse($comentarios as $comentario)
                        <article class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                            <div class="mb-2 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-slate-900 dark:text-white">{{ $comentario->membro->nome ?? $comentario->user->name ?? 'Leitor' }}</p>
                                    <p class="text-[10px] uppercase tracking-widest text-slate-500">{{ $comentario->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="flex items-center gap-1 text-xs text-amber-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="ph {{ $comentario->nota >= $i ? 'ph-fill ph-star' : 'ph-star text-slate-300 dark:text-slate-700' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $comentario->comentario }}</p>

                            @php
                                $isOwner = ($isMembroOperacional && auth()->guard('membro')->id() === $comentario->membro_id)
                                    || (auth()->guard('web')->check() && auth()->guard('web')->id() === $comentario->user_id);
                            @endphp
                            @if($isOwner)
                                <details class="mt-4">
                                    <summary class="cursor-pointer text-[11px] font-black uppercase tracking-widest text-amber-700 dark:text-amber-300">Editar comentário</summary>
                                    <form action="{{ route('livros.comentarios.update', [$livro->id, $comentario->id]) }}" method="POST" class="mt-3" data-confirm="comment" data-title="Salvar alteração?" data-text="Seu comentário será atualizado.">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-[180px_minmax(0,1fr)]">
                                            <div>
                                                <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500" for="nota-{{ $comentario->id }}">Nota</label>
                                                <select id="nota-{{ $comentario->id }}" name="nota" class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-200">
                                                    <option value="5" @selected($comentario->nota == 5)>5 - Excelente</option>
                                                    <option value="4" @selected($comentario->nota == 4)>4 - Muito bom</option>
                                                    <option value="3" @selected($comentario->nota == 3)>3 - Bom</option>
                                                    <option value="2" @selected($comentario->nota == 2)>2 - Regular</option>
                                                    <option value="1" @selected($comentario->nota == 1)>1 - Ruim</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500" for="comentario-{{ $comentario->id }}">Comentário</label>
                                                <textarea id="comentario-{{ $comentario->id }}" name="comentario" rows="4" class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-200">{{ $comentario->comentario }}</textarea>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                                                Salvar
                                            </button>
                                        </div>
                                    </form>
                                    <form action="{{ route('livros.comentarios.destroy', [$livro->id, $comentario->id]) }}" method="POST" class="mt-3" data-confirm="delete" data-title="Excluir comentário?" data-text="Essa ação não pode ser desfeita.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-md border border-red-200 bg-red-50 px-4 text-[11px] font-black uppercase tracking-widest text-red-700 transition hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20">
                                            Excluir
                                        </button>
                                    </form>
                                </details>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">
                            Ainda não há comentários.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
