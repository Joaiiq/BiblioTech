<div x-data="{ open: false }" @keydown.escape="open = false" @openmodal.window="open = true">
    <!-- Botão flutuante mobile -->
    <button @click="open = true" class="sm:hidden fixed bottom-24 right-6 z-40 w-12 h-12 rounded-full bg-emerald-600 text-white shadow-lg hover:bg-emerald-700 transition-all flex items-center justify-center">
        <i class="ph ph-list text-xl"></i>
    </button>

    <!-- Overlay -->
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50" @click="open = false"></div>

    <!-- Modal -->
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-12 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-12 opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="open = false">
        <div class="w-full max-w-6xl max-h-[90vh] bg-[#0d1420] border border-white/[.06] rounded-2xl shadow-2xl shadow-black/50 flex flex-col overflow-hidden">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/5">
                <div>
                    <h3 class="text-2xl font-black text-white font-serif">Todos os Livros</h3>
                    <p id="modal-count" class="text-xs text-gray-500 mt-1"></p>
                </div>
                <button @click="open = false" class="w-10 h-10 rounded-lg flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/5 transition">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <!-- Filtros -->
            <div class="p-6 border-b border-white/5 bg-[#080d14]">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="filter-label" for="modal-search">Buscar</label>
                        <div class="filter-input-wrap">
                            <i class="ph ph-magnifying-glass filter-icon"></i>
                            <input type="text" id="modal-search" class="filter-input" placeholder="Título, autor...">
                        </div>
                    </div>
                    <div>
                        <label class="filter-label" for="modal-categoria">Categoria</label>
                        <select id="modal-categoria" placeholder="Todas...">
                            <option value="">Todas as categorias</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="filter-label" for="modal-autor">Autor</label>
                        <select id="modal-autor" placeholder="Todos...">
                            <option value="">Todos os autores</option>
                            @foreach($autores as $autor)
                            <option value="{{ $autor->id }}">{{ $autor->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="filter-label" for="modal-sort">Ordenar</label>
                        <select id="modal-sort">
                            <option value="recente">Mais Recentes</option>
                            <option value="titulo_az">Título A → Z</option>
                            <option value="titulo_za">Título Z → A</option>
                            <option value="bestseller">Destaques</option>
                        </select>
                    </div>
                </div>
                <div id="modal-active-filters" class="mt-4 flex flex-wrap gap-2" style="display:none!important"></div>
            </div>

            <!-- Grid de livros -->
            <div class="flex-1 overflow-y-auto">
                <div id="modal-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 p-6">
                    @foreach($livros as $livro)
                    <div class="modal-book-card group bg-[#080d14] rounded-xl overflow-hidden border border-white/5 hover:border-emerald-500/30 transition-all flex flex-col"
                         data-titulo="{{ strtolower($livro->titulo) }}"
                         data-autor-nome="{{ strtolower($livro->autor->nome ?? '') }}"
                         data-autor-id="{{ $livro->autor_id }}"
                         data-categoria="{{ $livro->categoriasFiltro() ?: 'Geral' }}"
                         data-data="{{ $livro->data_publicacao }}"
                         data-bestseller="{{ $livro->e_bestseller ? 1 : 0 }}">
                        <a href="{{ route('livros.show', $livro->id) }}" class="flex-grow flex flex-col">
                            <div class="book-cover-wrap relative">
                                @if($livro->capa)
                                    <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" loading="lazy" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-emerald-900/20 to-[#080d14]"><i class="ph ph-book text-3xl text-emerald-900/40"></i></div>
                                @endif
                                @if($livro->e_bestseller)
                                <div class="bestseller-badge">★ Destaque</div>
                                @endif
                                <div class="absolute inset-0 bg-[#080d14]/94 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center text-center p-5 gap-3">
                                    <p class="text-gray-300 text-[11px] leading-relaxed line-clamp-3">{{ $livro->sinopse ?? 'Sinopse não disponível.' }}</p>
                                    <span class="px-4 py-1.5 bg-emerald-600 text-white rounded-lg text-[10px] font-semibold shrink-0">Ver</span>
                                </div>
                            </div>
                            <div class="p-3 flex-grow flex flex-col gap-0.5">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-emerald-500/70">{{ $livro->categoriasTexto() }}</span>
                                <h4 class="text-white text-xs font-semibold truncate group-hover:text-emerald-400 transition-colors">{{ $livro->titulo }}</h4>
                                <p class="text-gray-500 text-[10px] truncate">{{ $livro->autor->nome ?? 'Não informado' }}</p>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
                <div id="modal-empty" style="display:none" class="flex items-center justify-center h-64">
                    <div class="text-center">
                        <i class="ph ph-magnifying-glass text-4xl text-gray-700 mb-3 block"></i>
                        <p class="text-gray-500">Nenhum livro encontrado</p>
                        <button id="modal-clear" class="mt-3 text-xs font-bold uppercase text-emerald-400 hover:text-emerald-300 transition">Limpar filtros</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const grid = document.getElementById('modal-grid');
        const allCards = [...grid.querySelectorAll('.modal-book-card')];
        const emptyEl = document.getElementById('modal-empty');
        const countEl = document.getElementById('modal-count');
        const clearBtn = document.getElementById('modal-clear');
        const chipsEl = document.getElementById('modal-active-filters');
        
        const tsCfg = {allowEmptyOption: true, create: false, maxOptions: 100};
        const tsCategoria = new TomSelect('#modal-categoria', {...tsCfg});
        const tsAutor = new TomSelect('#modal-autor', {...tsCfg, searchField: ['text']});
        const tsSort = new TomSelect('#modal-sort', {create: false, allowEmptyOption: false});

        function applyFilters() {
            const search = document.getElementById('modal-search').value.toLowerCase().trim();
            const categoria = tsCategoria.getValue();
            const autorId = String(tsAutor.getValue());
            const sort = tsSort.getValue();
            let visible = 0;
            
            allCards.forEach(c => {
                const ok = (!search || c.dataset.titulo.includes(search) || c.dataset.autorNome.includes(search)) &&
                           (!categoria || (c.dataset.categoria || '').split('|').includes(categoria)) &&
                           (!autorId || c.dataset.autorId === autorId);
                c.style.display = ok ? '' : 'none';
                if(ok) visible++;
            });

            [...allCards]
                .filter(c => c.style.display !== 'none')
                .sort((a, b) => {
                    if(sort === 'titulo_az') return a.dataset.titulo.localeCompare(b.dataset.titulo, 'pt-BR');
                    if(sort === 'titulo_za') return b.dataset.titulo.localeCompare(a.dataset.titulo, 'pt-BR');
                    if(sort === 'bestseller') return parseInt(b.dataset.bestseller) - parseInt(a.dataset.bestseller);
                    return b.dataset.data.localeCompare(a.dataset.data);
                })
                .forEach(c => grid.appendChild(c));

            countEl.textContent = `${visible.toLocaleString('pt-BR')} título${visible !== 1 ? 's' : ''}`;
            emptyEl.style.display = visible === 0 ? 'block' : 'none';

            // Chips
            chipsEl.innerHTML = '';
            const chips = [];
            if(search) chips.push({label: `"${search}"`, clear: () => {document.getElementById('modal-search').value = ''; applyFilters();}});
            if(categoria) chips.push({label: categoria, clear: () => tsCategoria.setValue('')});
            if(autorId) chips.push({label: tsAutor.getOption(autorId)?.textContent?.trim() || 'Autor', clear: () => tsAutor.setValue('')});
            
            if(chips.length) {
                chips.forEach(({label, clear}) => {
                    const b = document.createElement('button');
                    b.className = 'filter-chip';
                    b.innerHTML = `<span>${label}</span><i class="ph ph-x" style="font-size:.75rem;color:#60a5fa"></i>`;
                    b.addEventListener('click', clear);
                    chipsEl.appendChild(b);
                });
                chipsEl.style.setProperty('display', 'flex', 'important');
            } else {
                chipsEl.style.setProperty('display', 'none', 'important');
            }

            if(visible > 0) {
                gsap.fromTo([...allCards].filter(c => c.style.display !== 'none'),
                    {opacity: 0, y: 8},
                    {opacity: 1, y: 0, duration: .28, stagger: .025, ease: 'power2.out', clearProps: 'transform'}
                );
            }
        }

        function clearAll(){
            document.getElementById('modal-search').value = '';
            tsCategoria.setValue('');
            tsAutor.setValue('');
            tsSort.setValue('recente');
            applyFilters();
        }

        let dbTimer;
        document.getElementById('modal-search').addEventListener('input', () => {
            clearTimeout(dbTimer);
            dbTimer = setTimeout(applyFilters, 250);
        });
        tsCategoria.on('change', applyFilters);
        tsAutor.on('change', applyFilters);
        tsSort.on('change', applyFilters);
        clearBtn.addEventListener('click', clearAll);
        applyFilters();
    });
    </script>
</div>
