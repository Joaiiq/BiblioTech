@php
    $isEdit = isset($autor) && $autor?->exists;
    $photoUrl = $isEdit && $autor->foto ? asset('storage/' . $autor->foto) : '';
    $nomeInicial = old('nome', $isEdit ? $autor->nome : '');
    $nacionalidadeInicial = old('nacionalidade', $isEdit ? $autor->nacionalidade : '');
    $dataInicial = old('data_nascimento', $isEdit && $autor->data_nascimento ? $autor->data_nascimento->format('Y-m-d') : '');
    $biografiaInicial = old('biografia', $isEdit ? $autor->biografia : '');
@endphp

<div
    x-data="{
        nome: @js($nomeInicial),
        nacionalidade: @js($nacionalidadeInicial),
        dataNascimento: @js($dataInicial),
        biografia: @js($biografiaInicial),
        photoPreview: @js($photoUrl),
        updatePhoto(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.photoPreview = URL.createObjectURL(file);
        },
        formattedDate() {
            if (!this.dataNascimento) return 'Não informada';
            const [year, month, day] = this.dataNascimento.split('-');
            return `${day}/${month}/${year}`;
        }
    }"
    class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_380px]"
>
    <section class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
        <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
            <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Ficha do autor</p>
            <h2 class="font-serif text-xl font-black text-slate-950 dark:text-white">{{ $title }}</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
        </div>

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5 p-5">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="nome" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Nome</label>
                    <input
                        id="nome"
                        type="text"
                        name="nome"
                        x-model="nome"
                        required
                        autofocus
                        class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-100"
                    >
                    <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                </div>

                <div>
                    <label for="nacionalidade" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Nacionalidade</label>
                    <input
                        id="nacionalidade"
                        type="text"
                        name="nacionalidade"
                        x-model="nacionalidade"
                        placeholder="Brasileira, inglesa, francesa..."
                        class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-100"
                    >
                    <x-input-error :messages="$errors->get('nacionalidade')" class="mt-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-[220px_minmax(0,1fr)]">
                <div>
                    <label for="data_nascimento" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Nascimento</label>
                    <input
                        id="data_nascimento"
                        type="date"
                        name="data_nascimento"
                        x-model="dataNascimento"
                        min="1000-01-01"
                        max="{{ today()->toDateString() }}"
                        class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-100"
                    >
                    <x-input-error :messages="$errors->get('data_nascimento')" class="mt-2" />
                </div>

                <div>
                    <label for="foto" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Foto</label>
                    <input
                        id="foto"
                        type="file"
                        name="foto"
                        accept="image/*"
                        x-on:change="updatePhoto($event)"
                        class="block h-11 w-full cursor-pointer rounded-md border border-dashed border-slate-300 bg-slate-50 text-sm text-slate-600 file:mr-2 sm:file:mr-4 file:h-full file:border-0 file:bg-[#1E3A8A] file:px-3 sm:file:px-4 file:text-[9px] sm:file:text-[11px] file:font-black file:uppercase file:tracking-widest file:text-white hover:border-amber-400 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-300"
                    >
                    <x-input-error :messages="$errors->get('foto')" class="mt-2" />
                </div>
            </div>

            <div>
                <label for="biografia" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Biografia</label>
                <textarea
                    id="biografia"
                    name="biografia"
                    rows="8"
                    x-model="biografia"
                    placeholder="Contexto literário, principais obras, período, curiosidades relevantes..."
                    class="w-full rounded-md border border-slate-200 bg-white px-3 py-3 text-sm leading-6 text-slate-900 placeholder:text-slate-400 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-100"
                ></textarea>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                    <x-input-error :messages="$errors->get('biografia')" />
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                        <span x-text="biografia.length"></span> caracteres
                    </span>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 dark:border-white/10 sm:flex-row sm:justify-end">
                <a href="{{ route('autores.index') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-5 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-arrow-left"></i>
                    Voltar
                </a>
                <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-6 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                    <i class="ph ph-floppy-disk"></i>
                    {{ $submitLabel }}
                </button>
            </div>
        </form>
    </section>

    <aside class="space-y-4">
        <section class="sticky top-6 rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
            <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Prévia</p>
            <div class="mt-5 flex flex-col items-center text-center">
                <div class="h-28 w-28 overflow-hidden rounded-md border border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-white/5">
                    <template x-if="photoPreview">
                        <img :src="photoPreview" alt="" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!photoPreview">
                        <div class="flex h-full w-full items-center justify-center">
                            <i class="ph ph-user text-4xl text-slate-400 dark:text-slate-500"></i>
                        </div>
                    </template>
                </div>

                <h3 class="mt-4 font-serif text-2xl font-black text-slate-950 dark:text-white" x-text="nome || 'Nome do autor'"></h3>
                <p class="mt-1 text-sm font-bold text-blue-700 dark:text-blue-300" x-text="nacionalidade || 'Nacionalidade não informada'"></p>
                <p class="mt-3 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-400">
                    Nascimento: <span x-text="formattedDate()"></span>
                </p>

                <p class="mt-4 line-clamp-6 text-sm leading-6 text-slate-600 dark:text-slate-300" x-text="biografia || 'A biografia ajuda o leitor a entender o contexto do autor antes de explorar os livros vinculados.'"></p>
            </div>
        </section>

        <section class="rounded-md border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
            <div class="flex gap-3">
                <i class="ph ph-lightbulb text-xl text-amber-700 dark:text-amber-300"></i>
                <div>
                    <p class="text-sm font-black text-amber-900 dark:text-amber-100">Dica de catálogo</p>
                    <p class="mt-1 text-xs leading-5 text-amber-800/80 dark:text-amber-100/70">Preencha biografias curtas e objetivas. Elas aparecem melhor na vitrine e deixam a busca do acervo mais interessante.</p>
                </div>
            </div>
        </section>
    </aside>
</div>
