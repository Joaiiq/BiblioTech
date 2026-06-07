@php
    $isEdit = isset($livro);
    $formAction = $isEdit ? route('livros.update', $livro->id) : route('livros.store');
    $formTitle = $isEdit ? 'Editar livro' : 'Novo livro';
    $formSubtitle = $isEdit ? 'Atualize dados editoriais, estoque e localização da obra.' : 'Cadastre obra, estoque e dados editoriais em uma ficha completa.';
    $submitLabel = $isEdit ? 'Atualizar livro' : 'Salvar livro';
    $coverUrl = $isEdit && $livro->capa ? asset('storage/' . $livro->capa) : '';
    $selectedAutor = old('autor_id', $isEdit ? $livro->autor_id : null);
    $selectedCategorias = collect(old('categorias', $isEdit ? $livro->categorias->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->values();
    if ($selectedCategorias->isEmpty() && $isEdit && $livro->categoria) {
        $categoriaCompat = $categorias->firstWhere('nome', $livro->categoria);
        if ($categoriaCompat) {
            $selectedCategorias = collect([(string) $categoriaCompat->id]);
        }
    }
    $selectedCategoriaLabel = $categorias
        ->whereIn('id', $selectedCategorias->map(fn ($id) => (int) $id)->all())
        ->pluck('nome')
        ->join(' / ');
    $isBestseller = (bool) old('e_bestseller', $isEdit ? $livro->e_bestseller : false);
    $isNacional = (bool) old('e_nacional', $isEdit ? $livro->e_nacional : false);
    $inputClass = 'block h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-white';
    $labelClass = 'mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400';
    $requiredMark = '<span class="ml-1 text-red-500">*</span>';
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6" data-confirm="loan" data-title="{{ $isEdit ? 'Atualizar livro?' : 'Cadastrar livro?' }}" data-text="Revise os dados antes de confirmar." data-submit-label="{{ $submitLabel }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95 sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Identificação</p>
                    <h2 class="font-serif text-2xl font-black text-slate-950 dark:text-white">{{ $formTitle }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $formSubtitle }}</p>
                </div>
                <span class="hidden rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 sm:inline-flex">
                    <i class="ph ph-book-open mr-1"></i>
                    Acervo
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="titulo" class="{{ $labelClass }}">{!! 'Título' . $requiredMark !!}</label>
                    <input id="titulo" type="text" name="titulo" value="{{ old('titulo', $isEdit ? $livro->titulo : '') }}" required autofocus class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                </div>

                <div>
                    <div class="mb-1.5 flex items-center justify-between gap-3">
                        <label for="autor_id" class="{{ $labelClass }} mb-0">{!! 'Autor' . $requiredMark !!}</label>
                        <button type="button" id="open-autor-modal" class="inline-flex h-8 items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 text-[10px] font-black uppercase tracking-widest text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">
                            <i class="ph ph-plus-circle"></i>
                            Novo autor
                        </button>
                    </div>
                    <select id="autor_id" name="autor_id" required class="{{ $inputClass }}">
                        <option value="">Selecione um autor</option>
                        @foreach($autores as $autor)
                            <option value="{{ $autor->id }}" @selected((string) $selectedAutor === (string) $autor->id)>{{ $autor->nome }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('autor_id')" class="mt-2" />
                </div>

                <div>
                    <label class="{{ $labelClass }}">{!! 'Categorias' . $requiredMark !!}</label>
                    <div id="categorias-wrapper" class="grid max-h-40 grid-cols-1 gap-2 overflow-y-auto rounded-md border border-slate-200 bg-white p-2 dark:border-white/10 dark:bg-[#080d14] sm:grid-cols-2">
                        @foreach($categorias as $cat)
                            <label class="flex cursor-pointer items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-200 dark:hover:bg-white/10">
                                <input type="checkbox" name="categorias[]" value="{{ $cat->id }}" @checked($selectedCategorias->contains((string) $cat->id)) class="rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1E3A8A] dark:border-white/10 dark:bg-[#080d14]">
                                <span>{{ $cat->nome }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('categorias.index') }}" class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-widest text-amber-700 hover:text-amber-900 dark:text-amber-300">
                            <i class="ph ph-tag"></i>
                            Gerenciar categorias
                        </a>
                    </div>
                    <x-input-error :messages="$errors->get('categorias')" class="mt-2" />
                    <x-input-error :messages="$errors->get('categorias.*')" class="mt-2" />
                </div>

                <div>
                    <label for="data_publicacao" class="{{ $labelClass }}">{!! 'Publicação' . $requiredMark !!}</label>
                    <input id="data_publicacao" type="date" name="data_publicacao" value="{{ old('data_publicacao', $isEdit && $livro->data_publicacao ? (is_string($livro->data_publicacao) ? $livro->data_publicacao : $livro->data_publicacao->format('Y-m-d')) : '') }}" min="1450-01-01" max="{{ today()->toDateString() }}" required class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('data_publicacao')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95 sm:p-6">
            <div class="mb-5">
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Estoque e localização</p>
                <h2 class="font-serif text-xl font-black text-slate-950 dark:text-white">Controle físico</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label for="quantidade" class="{{ $labelClass }}">{!! 'Exemplares' . $requiredMark !!}</label>
                    <input id="quantidade" type="number" name="quantidade" min="0" value="{{ old('quantidade', $isEdit ? $livro->quantidade : '') }}" required class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('quantidade')" class="mt-2" />
                </div>
                <div>
                    <label for="estante" class="{{ $labelClass }}">Estante</label>
                    <input id="estante" type="text" name="estante" value="{{ old('estante', $isEdit ? $livro->estante : '') }}" placeholder="A3" class="{{ $inputClass }}">
                </div>
                <div class="md:col-span-2">
                    <label for="localizacao" class="{{ $labelClass }}">Localização física</label>
                    <input id="localizacao" type="text" name="localizacao" value="{{ old('localizacao', $isEdit ? $livro->localizacao : '') }}" placeholder="Corredor 2, prateleira superior" class="{{ $inputClass }}">
                </div>
            </div>
        </section>

        <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95 sm:p-6">
            <div class="mb-5">
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Dados editoriais</p>
                <h2 class="font-serif text-xl font-black text-slate-950 dark:text-white">Publicação e conteúdo</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="isbn" class="{{ $labelClass }}">{!! 'ISBN' . $requiredMark !!}</label>
                    <input id="isbn" type="text" name="isbn" value="{{ old('isbn', $isEdit ? $livro->isbn : '') }}" required placeholder="000-00-000-0000-0" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('isbn')" class="mt-2" />
                </div>
                <div>
                    <label for="editora" class="{{ $labelClass }}">Editora</label>
                    <input id="editora" type="text" name="editora" value="{{ old('editora', $isEdit ? $livro->editora : '') }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <label for="paginas" class="{{ $labelClass }}">Páginas</label>
                    <input id="paginas" type="number" name="paginas" min="1" value="{{ old('paginas', $isEdit ? $livro->paginas : '') }}" class="{{ $inputClass }}">
                </div>
            </div>

            <div class="mt-4">
                <label for="sinopse" class="{{ $labelClass }}">Sinopse</label>
                <textarea id="sinopse" name="sinopse" rows="6" maxlength="3000" class="block w-full rounded-md border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-white">{{ old('sinopse', $isEdit ? $livro->sinopse : '') }}</textarea>
                <p class="mt-1 text-right text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                    <span id="sinopse-left">3000</span> caracteres restantes
                </p>
                <x-input-error :messages="$errors->get('sinopse')" class="mt-2" />
            </div>

            <div class="mt-4">
                <label for="preview" class="{{ $labelClass }}">Prévia das páginas</label>
                <textarea id="preview" name="preview" rows="5" placeholder="Trecho do livro para prévia..." class="block w-full rounded-md border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-white">{{ old('preview', $isEdit ? $livro->preview : '') }}</textarea>
            </div>

            <div class="mt-4 rounded-md border border-dashed border-slate-300 bg-slate-50 p-4 dark:border-white/10 dark:bg-[#080d14]">
                <label for="preview_pdf" class="{{ $labelClass }}">Prévia em PDF opcional</label>
                <input id="preview_pdf" type="file" name="preview_pdf" accept="application/pdf,.pdf" class="block w-full rounded-md border border-slate-200 bg-white p-3 text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-[#1E3A8A] file:px-4 file:py-2 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:text-white hover:border-amber-300 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-300">
                <div class="mt-2 flex flex-col gap-2 text-xs text-slate-500 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                    <span>PDF até 10MB. Use poucas páginas para demonstrar a obra.</span>
                    @if($isEdit && $livro->preview_pdf)
                        <a href="{{ asset('storage/' . $livro->preview_pdf) }}" target="_blank" class="inline-flex items-center gap-1 font-black uppercase tracking-widest text-amber-700 hover:text-amber-900 dark:text-amber-300">
                            <i class="ph ph-file-pdf"></i>
                            Ver PDF atual
                        </a>
                    @endif
                </div>
                <x-input-error :messages="$errors->get('preview_pdf')" class="mt-2" />
            </div>
        </section>

        <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95 sm:p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_220px] md:items-center">
                <div>
                    <label for="capa" class="{{ $labelClass }}">{{ $isEdit ? 'Trocar capa' : 'Capa do livro' }}</label>
                    <input id="capa" type="file" name="capa" accept="image/*" class="block w-full rounded-md border border-dashed border-slate-300 bg-slate-50 p-3 text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-[#1E3A8A] file:px-4 file:py-2 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:text-white hover:border-amber-300 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-300">
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">PNG ou JPG até 2MB. O preview atualiza antes de salvar.</p>
                    <x-input-error :messages="$errors->get('capa')" class="mt-2" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label for="e_bestseller" class="flex h-16 cursor-pointer items-center justify-between gap-4 rounded-md border border-amber-200 bg-amber-50 px-4 text-amber-900 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200 dark:hover:bg-amber-500/20">
                        <span>
                            <span class="block text-[10px] font-black uppercase tracking-widest">Destaque</span>
                            <span class="text-sm font-black">Destaque do acervo</span>
                        </span>
                        <input id="e_bestseller" type="checkbox" name="e_bestseller" value="1" @checked($isBestseller) class="h-5 w-5 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                    </label>
                    <label for="e_nacional" class="flex h-16 cursor-pointer items-center justify-between gap-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 text-emerald-900 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200 dark:hover:bg-emerald-500/20">
                        <span>
                            <span class="block text-[10px] font-black uppercase tracking-widest">Livro nacional</span>
                            <span class="text-sm font-black">Destaque para obras nacionais</span>
                        </span>
                        <input id="e_nacional" type="checkbox" name="e_nacional" value="1" @checked($isNacional) class="h-5 w-5 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                    </label>
                </div>
            </div>
        </section>

        <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 dark:border-white/10 sm:flex-row sm:justify-end">
            <a href="{{ route('dashboard') }}" class="inline-flex h-11 items-center justify-center rounded-md border border-slate-200 bg-slate-50 px-5 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                Cancelar
            </a>
            <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-6 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                <i class="ph ph-floppy-disk"></i>
                {{ $submitLabel }}
            </button>
        </div>
    </form>

    <aside class="lg:sticky lg:top-24 lg:self-start">
        <div class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
            <p class="mb-4 text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Pré-visualização</p>
            <div class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg dark:border-white/10 dark:bg-[#080d14]">
                <div class="relative h-64 bg-slate-100 dark:bg-[#0d1420]">
                    <span id="prev-placeholder" class="{{ $coverUrl ? 'hidden' : 'flex' }} h-full w-full flex-col items-center justify-center text-xs font-black uppercase tracking-widest text-slate-400">
                        <i class="ph ph-image text-3xl"></i>
                        Sem capa
                    </span>
                    <img id="prev-img" src="{{ $coverUrl }}" alt="Capa" class="{{ $coverUrl ? '' : 'hidden' }} h-full w-full object-cover">
                    <div class="absolute right-3 top-3 flex flex-col items-end gap-2">
                        <span id="prev-badge" class="{{ $isBestseller ? '' : 'hidden' }} rounded-md bg-[#F59E0B] px-2 py-1 text-[10px] font-black uppercase tracking-widest text-slate-950">Destaque</span>
                        <span id="prev-national-badge" class="{{ $isNacional ? '' : 'hidden' }} rounded-md bg-emerald-500 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-white">Nacional</span>
                    </div>
                    <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between gap-2">
                        <span id="prev-cat" class="truncate rounded-md bg-white/90 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-slate-800">{{ $selectedCategoriaLabel ?: 'Categoria' }}</span>
                        <span class="rounded-md bg-emerald-600 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-white"><span id="prev-stock">{{ old('quantidade', $isEdit ? $livro->quantidade : 0) ?: 0 }}</span> ex.</span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span id="prev-ano" class="text-[10px] font-black uppercase tracking-widest text-slate-500">{{ old('data_publicacao', $isEdit ? $livro->data_publicacao : '') ? date('Y', strtotime(old('data_publicacao', $isEdit ? $livro->data_publicacao : ''))) : '----' }}</span>
                        <span id="prev-location" class="truncate text-[10px] font-black uppercase tracking-widest text-amber-700 dark:text-amber-300">{{ old('estante', $isEdit ? $livro->estante : '') ?: 'Sem estante' }}</span>
                    </div>
                    <h3 id="prev-title" class="line-clamp-2 font-serif text-xl font-black leading-tight text-slate-950 dark:text-white">{{ old('titulo', $isEdit ? $livro->titulo : '') ?: 'Título do livro' }}</h3>
                    <p id="prev-author" class="mt-1 truncate text-sm font-semibold text-slate-600 dark:text-slate-400">{{ $selectedAutor ? optional($autores->firstWhere('id', (int) $selectedAutor))->nome : 'Autor da obra' }}</p>
                    <p id="prev-synopsis" class="mt-4 line-clamp-4 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ old('sinopse', $isEdit ? $livro->sinopse : '') ?: 'A sinopse aparece aqui para conferir o card antes de salvar.' }}</p>
                    <div class="mt-4 border-t border-slate-200 pt-3 text-[10px] font-mono text-slate-500 dark:border-white/10">
                        ISBN: <span id="prev-isbn">{{ old('isbn', $isEdit ? $livro->isbn : '') ?: '000-00-000-0000-0' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>

<div id="autor-modal" class="fixed inset-0 z-[11040] hidden">
    <div id="autor-modal-backdrop" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>
    <div class="relative mx-auto mt-10 w-full max-w-2xl px-4 sm:mt-16">
        <div class="rounded-md border border-slate-200 bg-white p-5 shadow-2xl dark:border-white/10 dark:bg-[#0d1420] sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Cadastro rápido</p>
                    <h3 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Novo autor</h3>
                </div>
                <button type="button" id="close-autor-modal" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-x"></i>
                </button>
            </div>

            <form id="autor-inline-form" class="mt-6 space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="autor-inline-nome" class="{{ $labelClass }}">{!! 'Nome do autor' . $requiredMark !!}</label>
                        <input id="autor-inline-nome" name="nome" type="text" required class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="autor-inline-nacionalidade" class="{{ $labelClass }}">Nacionalidade</label>
                        <input id="autor-inline-nacionalidade" name="nacionalidade" type="text" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="autor-inline-data" class="{{ $labelClass }}">Data de nascimento</label>
                        <input id="autor-inline-data" name="data_nascimento" type="date" max="{{ today()->toDateString() }}" class="{{ $inputClass }}">
                    </div>
                    <div class="md:col-span-2">
                        <label for="autor-inline-biografia" class="{{ $labelClass }}">Biografia</label>
                        <textarea id="autor-inline-biografia" name="biografia" rows="4" class="block w-full rounded-md border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-white"></textarea>
                    </div>
                </div>
                <div id="autor-inline-errors" class="hidden rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"></div>
                <div class="flex justify-end gap-3">
                    <button type="button" id="cancel-autor-modal" class="inline-flex h-11 items-center justify-center rounded-md border border-slate-200 bg-slate-50 px-5 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">Cancelar</button>
                    <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-emerald-600 px-6 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-emerald-700">
                        <i class="ph ph-floppy-disk"></i>
                        Salvar autor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/imask"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isbnInput = document.getElementById('isbn');
        if (isbnInput && window.IMask) IMask(isbnInput, { mask: '000-00-000-0000-0' });

        const text = (id, targetId, fallback, formatter = value => value) => {
            const input = document.getElementById(id);
            const target = document.getElementById(targetId);
            if (!input || !target) return;
            const sync = () => target.textContent = formatter(input.value.trim()) || fallback;
            input.addEventListener('input', sync);
            input.addEventListener('change', sync);
            sync();
        };

        text('titulo', 'prev-title', 'Título do livro');
        text('sinopse', 'prev-synopsis', 'A sinopse aparece aqui para conferir o card antes de salvar.');
        text('isbn', 'prev-isbn', '000-00-000-0000-0');
        text('quantidade', 'prev-stock', '0');
        text('estante', 'prev-location', 'Sem estante');

        const authorSelect = document.getElementById('autor_id');
        const prevAuthor = document.getElementById('prev-author');
        const syncAuthor = () => {
            if (!authorSelect || !prevAuthor) return;
            prevAuthor.textContent = authorSelect.options[authorSelect.selectedIndex]?.text || 'Autor da obra';
        };
        authorSelect?.addEventListener('change', syncAuthor);
        syncAuthor();

        const categoriaInputs = [...document.querySelectorAll('input[name="categorias[]"]')];
        const prevCat = document.getElementById('prev-cat');
        const syncCategories = () => {
            if (!prevCat) return;
            const labels = categoriaInputs
                .filter(input => input.checked)
                .map(input => input.closest('label')?.querySelector('span')?.textContent?.trim())
                .filter(Boolean);
            prevCat.textContent = labels.join(' / ') || 'Categoria';
        };
        categoriaInputs.forEach(input => input.addEventListener('change', syncCategories));
        syncCategories();

        const sinopse = document.getElementById('sinopse');
        const sinopseLeft = document.getElementById('sinopse-left');
        const syncSinopseLimit = () => {
            if (!sinopse || !sinopseLeft) return;
            sinopseLeft.textContent = Math.max(0, 3000 - sinopse.value.length);
        };
        sinopse?.addEventListener('input', syncSinopseLimit);
        syncSinopseLimit();

        const dateInput = document.getElementById('data_publicacao');
        const prevAno = document.getElementById('prev-ano');
        const syncYear = () => {
            if (!dateInput || !prevAno) return;
            prevAno.textContent = dateInput.value ? new Date(dateInput.value).getFullYear() : '----';
        };
        dateInput?.addEventListener('change', syncYear);
        syncYear();

        const bestsellerInput = document.getElementById('e_bestseller');
        const badge = document.getElementById('prev-badge');
        bestsellerInput?.addEventListener('change', event => badge?.classList.toggle('hidden', !event.target.checked));
        const nacionalInput = document.getElementById('e_nacional');
        const nacionalBadge = document.getElementById('prev-national-badge');
        nacionalInput?.addEventListener('change', event => nacionalBadge?.classList.toggle('hidden', !event.target.checked));

        const capaInput = document.getElementById('capa');
        capaInput?.addEventListener('change', event => {
            const file = event.target.files?.[0];
            const img = document.getElementById('prev-img');
            const placeholder = document.getElementById('prev-placeholder');
            if (!file || !img || !placeholder) return;

            const reader = new FileReader();
            reader.onload = readerEvent => {
                img.src = readerEvent.target.result;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
                placeholder.classList.remove('flex');
            };
            reader.readAsDataURL(file);
        });

        const autorModal = document.getElementById('autor-modal');
        const openAutorModal = document.getElementById('open-autor-modal');
        const closeAutorModal = document.getElementById('close-autor-modal');
        const cancelAutorModal = document.getElementById('cancel-autor-modal');
        const autorModalBackdrop = document.getElementById('autor-modal-backdrop');
        const autorInlineForm = document.getElementById('autor-inline-form');
        const autorInlineErrors = document.getElementById('autor-inline-errors');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const toggleAutorModal = (open) => {
            if (!autorModal) return;
            autorModal.classList.toggle('hidden', !open);
            document.body.classList.toggle('overflow-hidden', open);
        };

        [openAutorModal, closeAutorModal, cancelAutorModal, autorModalBackdrop].forEach(element => {
            element?.addEventListener('click', () => toggleAutorModal(element === openAutorModal));
        });

        autorInlineForm?.addEventListener('submit', async (event) => {
            event.preventDefault();

            autorInlineErrors?.classList.add('hidden');
            autorInlineErrors.textContent = '';

            const formData = new FormData(autorInlineForm);

            try {
                const response = await fetch(@json(route('autores.store')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const payload = await response.json();

                if (!response.ok) {
                    const messages = Object.values(payload.errors || {}).flat();
                    autorInlineErrors.textContent = messages[0] || payload.message || 'Não foi possível cadastrar o autor.';
                    autorInlineErrors.classList.remove('hidden');
                    return;
                }

                const option = new Option(payload.autor.nome, payload.autor.id, true, true);
                authorSelect?.appendChild(option);
                syncAuthor();
                autorInlineForm.reset();
                toggleAutorModal(false);

                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2600,
                        timerProgressBar: true,
                        background: '#0d1420',
                        color: '#fff',
                        icon: 'success',
                        title: payload.message || 'Autor cadastrado com sucesso.',
                    });
                }
            } catch (error) {
                autorInlineErrors.textContent = 'Falha ao cadastrar autor.';
                autorInlineErrors.classList.remove('hidden');
            }
        });
    });
</script>
