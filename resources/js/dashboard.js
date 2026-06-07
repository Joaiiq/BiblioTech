document.addEventListener('DOMContentLoaded', () => {
    const DARK = '#93c5fd';
    const LIGHT = '#F59E0B';

    function applyBgTheme() {
        const dark = document.documentElement.classList.contains('dark');
        const icons = document.querySelectorAll('.bg-icon');
        const dot = document.getElementById('bg-dot-circle');
        const glow1 = document.getElementById('bg-glow-1');
        const glow2 = document.getElementById('bg-glow-2');
        const shelves = document.querySelectorAll('.bg-shelf');

        icons.forEach(el => el.style.color = dark ? DARK : LIGHT);
        dot?.setAttribute('fill', dark ? DARK : '#1E3A8A');
        shelves.forEach(s => s.style.background = dark
            ? 'rgba(255,255,255,0.025)'
            : 'linear-gradient(90deg, transparent, rgba(30,58,138,0.12) 20%, rgba(245,158,11,0.22) 80%, transparent)');
        if (glow1) glow1.style.background = dark
            ? 'rgba(30,58,138,0.18)' : 'rgba(30,58,138,0.12)';
        if (glow2) glow2.style.background = dark
            ? 'rgba(245,158,11,0.09)' : 'rgba(245,158,11,0.14)';
    }

    document.querySelectorAll('.bg-icon').forEach(el => {
        gsap.set(el, { opacity: document.documentElement.classList.contains('dark') ? gsap.utils.random(0.03, 0.07) : gsap.utils.random(0.10, 0.16) });
        gsap.to(el, {
            y: gsap.utils.random(-8, -14),
            rotation: gsap.utils.random(-8, 8),
            opacity: document.documentElement.classList.contains('dark') ? gsap.utils.random(0.05, 0.09) : gsap.utils.random(0.12, 0.20),
            duration: gsap.utils.random(8, 20),
            repeat: -1,
            yoyo: true,
            ease: 'sine.inOut',
            delay: gsap.utils.random(0, 14),
        });
    });

    applyBgTheme();
    new MutationObserver(applyBgTheme)
        .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    const loansToggle = document.getElementById('loans-toggle');
    const loansSidebar = document.getElementById('loans-sidebar');
    const loansBackdrop = document.getElementById('loans-backdrop');
    const loansClose = document.getElementById('loans-close');

    const notificationsToggle = document.getElementById('notifications-toggle');
    const notificationsSidebar = document.getElementById('notifications-sidebar');
    const notificationsBackdrop = document.getElementById('notifications-backdrop');
    const notificationsClose = document.getElementById('notifications-close');

    // Show a toast on page load if there are unread notifications
    const notifBadge = document.getElementById('notifications-badge');
    if (notifBadge && typeof Swal !== 'undefined') {
        const cntText = notifBadge.textContent.trim();
        Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:3500,timerProgressBar:true,background:'#0d1420',color:'#fff',didOpen:t=>{t.onmouseenter=Swal.stopTimer;t.onmouseleave=Swal.resumeTimer;}}).fire({icon:'info',title: cntText + (cntText === '1' ? ' nova notificação' : ' novas notificações')});
    }

    function setLoansOpen(isOpen) {
        if (!loansSidebar || !loansBackdrop || !loansToggle) return;
        loansSidebar.classList.toggle('right-0', isOpen);
        loansSidebar.classList.toggle('right-[-420px]', !isOpen);
        loansBackdrop.classList.toggle('opacity-100', isOpen);
        loansBackdrop.classList.toggle('pointer-events-auto', isOpen);
        loansBackdrop.classList.toggle('pointer-events-none', !isOpen);
        document.body.classList.toggle('overflow-hidden', isOpen);
        loansToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    function setNotificationsOpen(isOpen) {
        if (!notificationsSidebar || !notificationsBackdrop || !notificationsToggle) return;
        notificationsSidebar.classList.toggle('right-0', isOpen);
        notificationsSidebar.classList.toggle('right-[-420px]', !isOpen);
        notificationsBackdrop.classList.toggle('opacity-100', isOpen);
        notificationsBackdrop.classList.toggle('pointer-events-auto', isOpen);
        notificationsBackdrop.classList.toggle('pointer-events-none', !isOpen);
        document.body.classList.toggle('overflow-hidden', isOpen);
        notificationsToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    loansToggle?.addEventListener('click', () => setLoansOpen(true));
    loansClose?.addEventListener('click', () => setLoansOpen(false));
    loansBackdrop?.addEventListener('click', () => setLoansOpen(false));

    notificationsToggle?.addEventListener('click', () => setNotificationsOpen(true));
    notificationsClose?.addEventListener('click', () => setNotificationsOpen(false));
    notificationsBackdrop?.addEventListener('click', () => setNotificationsOpen(false));

    // Marcar todas como lidas
    const markAllBtn = document.getElementById('mark-all-read');
    markAllBtn?.addEventListener('click', function () {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/notifications/mark-read', { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(() => {
                setNotificationsOpen(false);
                // remove badge
                const badge = document.getElementById('notifications-badge');
                if (badge) badge.remove();
                // convert unread items to read style
                document.querySelectorAll('.notification-unread').forEach(el => {
                    el.className = 'notification-read p-3 rounded-md bg-transparent border border-white/5 text-slate-400';
                });
                if (typeof Swal !== 'undefined') {
                    Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2500,timerProgressBar:true,background:'#0d1420',color:'#fff'}).fire({icon:'success',title:'Notificações marcadas como lidas'});
                }
                markAllBtn.textContent = 'Marcadas como lidas';
                markAllBtn.disabled = true;
            });
    });

    document.querySelectorAll('.progress-fill[data-progress]').forEach(el => {
        const pct = Number(el.dataset.progress || 0);
        el.style.width = `${Math.max(0, Math.min(100, pct))}%`;
    });

    function animateCounter(el) {
        const target = parseInt(el.dataset.target, 10) || 0;
        if (!target) {
            el.textContent = '0';
            return;
        }
        const dur = 1600;
        const start = performance.now();
        function step(now) {
            const e = Math.min(now - start, dur);
            const p = 1 - Math.pow(1 - e / dur, 3);
            el.textContent = Math.round(p * target).toLocaleString('pt-BR');
            if (e < dur) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString('pt-BR');
        }
        requestAnimationFrame(step);
    }
    document.querySelectorAll('.counter').forEach(animateCounter);

    gsap.registerPlugin(ScrollTrigger);
    gsap.from('#hero-txt', { opacity: 0, y: 28, duration: 0.8, ease: 'power3.out', delay: 0.05 });
    gsap.from('#hero-stats .stat-pill', { opacity: 0, y: 20, duration: 0.55, stagger: 0.1, ease: 'power2.out', delay: 0.2 });
    gsap.from('#filter-bar', { scrollTrigger: { trigger: '#filter-bar', start: 'top 88%', once: true }, opacity: 0, y: 20, duration: 0.55, ease: 'power2.out' });
    document.querySelectorAll('.gs-section h2').forEach(el => {
        gsap.from(el, { scrollTrigger: { trigger: el, start: 'top 88%', once: true }, opacity: 0, x: -18, duration: 0.55, ease: 'power2.out' });
    });

    new Swiper('#swiper-populares', {
        slidesPerView: 'auto',
        spaceBetween: 14,
        grabCursor: true,
        autoplay: { delay: 2600, disableOnInteraction: false, pauseOnMouseEnter: true },
        navigation: { nextEl: '#swiper-populares-next', prevEl: '#swiper-populares-prev' },
    });
    new Swiper('#swiper-recentes', {
        slidesPerView: 'auto',
        spaceBetween: 14,
        grabCursor: true,
        autoplay: { delay: 3000, disableOnInteraction: false, pauseOnMouseEnter: true },
        navigation: { nextEl: '#swiper-recentes-next', prevEl: '#swiper-recentes-prev' },
    });

    // Swiper para seção de acervo (mais livros além dos 8 primeiros)
    if (document.getElementById('swiper-acervo')) {
        new Swiper('#swiper-acervo', {
            slidesPerView: 'auto',
            spaceBetween: 14,
            grabCursor: true,
            autoplay: { delay: 3200, disableOnInteraction: false, pauseOnMouseEnter: true },
            navigation: { nextEl: '#swiper-acervo-next', prevEl: '#swiper-acervo-prev' },
        });
    }
    
    new Swiper('.swiperAutores', {
        loop: true,
        grabCursor: true,
        slidesPerView: 1.4,
        spaceBetween: 14,
        breakpoints: { 480: { slidesPerView: 2.5, spaceBetween: 16 }, 768: { slidesPerView: 3.5, spaceBetween: 18 }, 1024: { slidesPerView: 5, spaceBetween: 20 } },
        autoplay: { delay: 3600, disableOnInteraction: false, pauseOnMouseEnter: true },
        navigation: { nextEl: '#swiper-autores-next', prevEl: '#swiper-autores-prev' },
    });

    const tsCfg = { allowEmptyOption: true, create: false, maxOptions: 100, plugins: ['clear_button'], dropdownParent: 'body' };
    const tsCategoria = new TomSelect('#filter-categoria', { ...tsCfg });
    const tsAutor = new TomSelect('#filter-autor', { ...tsCfg, searchField: ['text'] });
    const tsDisponibilidade = new TomSelect('#filter-disponibilidade', { ...tsCfg });
    const tsDestaque = new TomSelect('#filter-destaque', { ...tsCfg });
    const tsSort = new TomSelect('#filter-sort', { create: false, allowEmptyOption: false });

    function styleTomSelect(instance) {
        const control = instance?.control;
        const dropdown = instance?.dropdown;
        control?.classList.add(
            'min-h-[44px]',
            'rounded-md',
            'border',
            'border-slate-200',
            'bg-white',
            'px-3',
            'py-2',
            'text-sm',
            'text-slate-800',
            'shadow-none',
            'transition',
            'focus-within:border-[#1E3A8A]',
            'focus-within:ring-2',
            'focus-within:ring-[#1E3A8A]/20',
            'dark:border-white/10',
            'dark:bg-[#080d14]',
            'dark:text-slate-200'
        );
        dropdown?.classList.add(
            'mt-1',
            'overflow-hidden',
            'rounded-md',
            'border',
            'border-slate-200',
            'bg-white',
            'text-slate-800',
            'shadow-xl',
            'shadow-slate-950/10',
            'dark:border-white/10',
            'dark:bg-[#080d14]',
            'dark:text-slate-200',
            'dark:shadow-black/40'
        );
    }

    [tsCategoria, tsAutor, tsDisponibilidade, tsDestaque, tsSort].forEach(styleTomSelect);

    const grid = document.getElementById('acervo-grid');
    const gridCards = grid ? [...grid.querySelectorAll('.acervo-card')] : [];
    const swiperBlock = document.getElementById('swiper-acervo-block');
    const swiperCards = [...document.querySelectorAll('#swiper-acervo .acervo-carousel-card')];
    const emptyEl = document.getElementById('empty-state');
    const countEl = document.getElementById('results-count');
    const clearBtn = document.getElementById('clear-all-btn');
    const clearBtn2 = document.getElementById('clear-filters-btn');
    const backDashboardBtn = document.getElementById('back-dashboard-btn');
    const chipsEl = document.getElementById('active-filters');
    const dashboardHome = document.getElementById('dashboard-home');
    const acervoSection = document.getElementById('acervo-section');

    function toggleAcervoView(shouldShow) {
        const showAcervo = Boolean(shouldShow);
        const wasHidden = acervoSection?.classList.contains('hidden');
        dashboardHome?.classList.toggle('hidden', showAcervo);
        acervoSection?.classList.toggle('hidden', !showAcervo);
        if (showAcervo && wasHidden) {
            acervoSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function applyFilters() {
        const search = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();
        const categoria = String(tsCategoria.getValue() || '').trim();
        const autorId = String(tsAutor.getValue() || '').trim();
        const disponibilidade = String(tsDisponibilidade.getValue() || '').trim();
        const destaque = String(tsDestaque.getValue() || '').trim();
        const sort = tsSort.getValue();
        const hasFilters = Boolean(search || categoria || autorId || disponibilidade || destaque);

        let visibleGrid = 0;
        let visibleSwiper = 0;

        gridCards.forEach(c => {
            const ok = (!search || c.dataset.titulo.includes(search) || c.dataset.autorNome.includes(search))
                && (!categoria || (c.dataset.categoria || '').split('|').includes(categoria))
                && (!autorId || String(c.dataset.autorId) === String(autorId))
                && (!disponibilidade || (disponibilidade === 'disponivel' ? c.dataset.disponivel === '1' : c.dataset.disponivel === '0'))
                && (!destaque || (
                    destaque === 'bestseller'
                        ? c.dataset.bestseller === '1'
                        : destaque === 'nacional'
                            ? c.dataset.nacional === '1'
                            : Number(c.dataset.reservas || 0) > 0
                ));
            c.style.display = ok ? '' : 'none';
            if (ok) visibleGrid++;
        });

        swiperCards.forEach(c => {
            const ok = (!search || c.dataset.titulo.includes(search) || c.dataset.autorNome.includes(search))
                && (!categoria || (c.dataset.categoria || '').split('|').includes(categoria))
                && (!autorId || String(c.dataset.autorId) === String(autorId))
                && (!disponibilidade || (disponibilidade === 'disponivel' ? c.dataset.disponivel === '1' : c.dataset.disponivel === '0'))
                && (!destaque || (
                    destaque === 'bestseller'
                        ? c.dataset.bestseller === '1'
                        : destaque === 'nacional'
                            ? c.dataset.nacional === '1'
                            : Number(c.dataset.reservas || 0) > 0
                ));
            c.style.display = ok ? '' : 'none';
            if (ok) visibleSwiper++;
        });

        if (grid) {
            gridCards.filter(c => c.style.display !== 'none').sort((a, b) => {
                if (sort === 'titulo_az') return a.dataset.titulo.localeCompare(b.dataset.titulo, 'pt-BR');
                if (sort === 'titulo_za') return b.dataset.titulo.localeCompare(a.dataset.titulo, 'pt-BR');
                if (sort === 'autor_az') return a.dataset.autorNome.localeCompare(b.dataset.autorNome, 'pt-BR');
                if (sort === 'bestseller') return parseInt(b.dataset.bestseller) - parseInt(a.dataset.bestseller);
                if (sort === 'disponiveis') return Number(b.dataset.quantidade || 0) - Number(a.dataset.quantidade || 0);
                if (sort === 'mais_emprestados') return Number(b.dataset.emprestimos || 0) - Number(a.dataset.emprestimos || 0);
                if (sort === 'mais_reservados') return Number(b.dataset.reservas || 0) - Number(a.dataset.reservas || 0);
                return Number(b.dataset.data || 0) - Number(a.dataset.data || 0);
            }).forEach(c => grid.appendChild(c));
        }
        const visible = visibleGrid + visibleSwiper;
        if (countEl) countEl.textContent = `${visible.toLocaleString('pt-BR')} título${visible !== 1 ? 's' : ''}`;
        if (emptyEl) emptyEl.classList.toggle('hidden', visible !== 0);
        if (clearBtn) {
            clearBtn.classList.toggle('hidden', !hasFilters);
            clearBtn.classList.toggle('inline-flex', hasFilters);
        }

        if (chipsEl) chipsEl.innerHTML = '';
        const chips = [];
        if (search) chips.push({ label: `"${search}"`, clear: () => { document.getElementById('filter-search').value = ''; applyFilters(); } });
        if (categoria) chips.push({ label: categoria, clear: () => tsCategoria.setValue('') });
        if (autorId) chips.push({ label: tsAutor.getOption(autorId)?.textContent?.trim() || 'Autor', clear: () => tsAutor.setValue('') });
        if (disponibilidade) chips.push({ label: disponibilidade === 'disponivel' ? 'Disponíveis' : 'Indisponíveis', clear: () => tsDisponibilidade.setValue('') });
        if (destaque) chips.push({ label: destaque === 'bestseller' ? 'Destaques' : destaque === 'nacional' ? 'Nacionais' : 'Com fila', clear: () => tsDestaque.setValue('') });
        if (chips.length && chipsEl) {
            chips.forEach(({ label, clear }) => {
                const b = document.createElement('button');
                b.className = 'inline-flex h-8 items-center gap-1.5 rounded-md border border-blue-200 bg-blue-50 px-3 text-[10px] font-black uppercase tracking-widest text-blue-700 transition hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20';
                b.innerHTML = `<span>${label}</span><i class="ph ph-x text-[0.75rem]"></i>`;
                b.addEventListener('click', clear);
                chipsEl.appendChild(b);
            });
            chipsEl.classList.remove('hidden');
        } else if (chipsEl) {
            chipsEl.classList.add('hidden');
        }
        if (visible > 0) {
            gsap.fromTo([...gridCards, ...swiperCards].filter(c => c.style.display !== 'none'),
                { opacity: 0, y: 8 },
                { opacity: 1, y: 0, duration: 0.28, stagger: 0.025, ease: 'power2.out', clearProps: 'transform' });
        }
        toggleAcervoView(hasFilters);
        if (swiperBlock) swiperBlock.classList.toggle('hidden', visibleSwiper === 0);
    }

    function setQuickButtonState() {
        const disponibilidade = String(tsDisponibilidade.getValue() || '').trim();
        const destaque = String(tsDestaque.getValue() || '').trim();
        document.querySelectorAll('[data-quick-filter]').forEach(btn => {
            const type = btn.getAttribute('data-quick-filter');
            const active = (type === 'disponivel' && disponibilidade === 'disponivel')
                || (type === 'bestseller' && destaque === 'bestseller')
                || (type === 'nacional' && destaque === 'nacional')
                || (type === 'fila' && destaque === 'fila');
            btn.classList.toggle('ring-2', active);
            btn.classList.toggle('ring-[#1E3A8A]', active);
            btn.classList.toggle('ring-offset-2', active);
            btn.classList.toggle('ring-offset-white', active);
            btn.classList.toggle('dark:ring-offset-[#0d1420]', active);
        });
    }

    document.querySelectorAll('[data-cat-filter]').forEach(btn => {
        btn.addEventListener('click', () => {
            const categoria = btn.getAttribute('data-cat-filter') || '';
            tsCategoria.setValue(categoria);
            applyFilters();
            requestAnimationFrame(() => {
                acervoSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    });

    document.querySelectorAll('[data-quick-filter]').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.getAttribute('data-quick-filter');
            if (type === 'disponivel') {
                tsDisponibilidade.setValue(tsDisponibilidade.getValue() === 'disponivel' ? '' : 'disponivel');
            }
            if (type === 'bestseller') {
                tsDestaque.setValue(tsDestaque.getValue() === 'bestseller' ? '' : 'bestseller');
            }
            if (type === 'nacional') {
                tsDestaque.setValue(tsDestaque.getValue() === 'nacional' ? '' : 'nacional');
            }
            if (type === 'fila') {
                tsDestaque.setValue(tsDestaque.getValue() === 'fila' ? '' : 'fila');
            }
            setQuickButtonState();
            applyFilters();
        });
    });

    function clearAll() {
        const topFilter = document.getElementById('top-filter');
        if (topFilter) topFilter.value = '';
        const searchEl = document.getElementById('filter-search');
        if (searchEl) searchEl.value = '';
        if (typeof tsCategoria !== 'undefined' && tsCategoria) tsCategoria.setValue('');
        if (typeof tsAutor !== 'undefined' && tsAutor) tsAutor.setValue('');
        if (typeof tsDisponibilidade !== 'undefined' && tsDisponibilidade) tsDisponibilidade.setValue('');
        if (typeof tsDestaque !== 'undefined' && tsDestaque) tsDestaque.setValue('');
        if (typeof tsSort !== 'undefined' && tsSort) tsSort.setValue('recente');
        globalSearchPanel?.classList.add('hidden');
        applyFilters();
    }

    let dbTimer;
    const searchInput = document.getElementById('filter-search');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(dbTimer);
            dbTimer = setTimeout(applyFilters, 250);
        });
    }
    const topFilterEl = document.getElementById('top-filter');
    const globalSearchPanel = document.getElementById('global-search-panel');
    const globalSearchResults = document.getElementById('global-search-results');
    const globalSearchEmpty = document.getElementById('global-search-empty');

    if (globalSearchPanel && globalSearchPanel.parentElement !== document.body) {
        document.body.appendChild(globalSearchPanel);
    }

    const normalizeText = value => (value || '')
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    const globalSearchItems = (() => {
        const books = new Map();
        [...gridCards, ...swiperCards].forEach(card => {
            const link = card.querySelector('a[href]');
            const title = card.querySelector('h4')?.textContent?.trim() || card.dataset.titulo || '';
            const author = card.querySelector('p')?.textContent?.trim() || card.dataset.autorNome || '';
            const href = link?.getAttribute('href');
            if (!href || books.has(href)) return;
            books.set(href, {
                type: 'Livro',
                icon: 'ph-book-open',
                title,
                subtitle: author || card.dataset.categoria || 'Acervo',
                href,
                search: normalizeText(`${title} ${author} ${card.dataset.categoria || ''}`),
            });
        });

        const authors = new Map();
        document.querySelectorAll('.swiperAutores a[href*="/autores/"]').forEach(link => {
            const card = link.closest('.swiper-slide');
            const title = link.querySelector('h4')?.textContent?.trim() || '';
            const subtitle = card?.querySelector('p')?.textContent?.trim() || 'Autor';
            const href = link.getAttribute('href');
            if (!href || !title || authors.has(href)) return;
            authors.set(href, {
                type: 'Autor',
                icon: 'ph-user',
                title,
                subtitle,
                href,
                search: normalizeText(`${title} ${subtitle}`),
            });
        });

        return [...books.values(), ...authors.values()];
    })();

    function renderGlobalSearch(query) {
        if (!globalSearchPanel || !globalSearchResults || !globalSearchEmpty) return;
        const clean = normalizeText(query);

        if (!clean) {
            globalSearchPanel.classList.add('hidden');
            return;
        }

        const results = globalSearchItems
            .filter(item => item.search.includes(clean))
            .slice(0, 8);

        globalSearchResults.innerHTML = '';
        globalSearchEmpty.classList.toggle('hidden', results.length > 0);

        results.forEach(item => {
            const link = document.createElement('a');
            link.href = item.href;
            link.className = 'flex items-center gap-3 rounded-md px-3 py-2 transition hover:bg-blue-50 dark:hover:bg-blue-500/10';
            link.innerHTML = `
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                    <i class="ph ${item.icon}"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-black text-slate-950 dark:text-white">${item.title}</span>
                    <span class="block truncate text-xs text-slate-500 dark:text-slate-400">${item.type} · ${item.subtitle}</span>
                </span>
                <i class="ph ph-arrow-right text-slate-400"></i>
            `;
            globalSearchResults.appendChild(link);
        });

        positionGlobalSearchPanel();
        globalSearchPanel.classList.remove('hidden');
    }

    function positionGlobalSearchPanel() {
        if (!globalSearchPanel || !topFilterEl) return;

        const rect = topFilterEl.getBoundingClientRect();
        globalSearchPanel.style.position = 'fixed';
        globalSearchPanel.style.left = `${rect.left}px`;
        globalSearchPanel.style.top = `${rect.bottom + 8}px`;
        globalSearchPanel.style.width = `${rect.width}px`;
        globalSearchPanel.style.zIndex = '2147483647';
    }

    if (topFilterEl) {
        topFilterEl.addEventListener('input', e => {
            const searchEl = document.getElementById('filter-search');
            if (!searchEl) return;
            searchEl.value = e.target.value;
            renderGlobalSearch(e.target.value);
            clearTimeout(dbTimer);
            dbTimer = setTimeout(applyFilters, 150);
        });
        topFilterEl.addEventListener('focus', e => renderGlobalSearch(e.target.value));
        topFilterEl.addEventListener('keydown', e => {
            if (e.key === 'Escape') globalSearchPanel?.classList.add('hidden');
        });
        window.addEventListener('resize', positionGlobalSearchPanel, { passive: true });
        window.addEventListener('scroll', positionGlobalSearchPanel, { passive: true });
    }

    document.addEventListener('click', e => {
        if (!globalSearchPanel || !topFilterEl) return;
        if (globalSearchPanel.contains(e.target) || topFilterEl.contains(e.target)) return;
        globalSearchPanel.classList.add('hidden');
    });

    if (typeof tsCategoria !== 'undefined' && tsCategoria) tsCategoria.on('change', () => { setQuickButtonState(); applyFilters(); });
    if (typeof tsAutor !== 'undefined' && tsAutor) tsAutor.on('change', () => { setQuickButtonState(); applyFilters(); });
    if (typeof tsDisponibilidade !== 'undefined' && tsDisponibilidade) tsDisponibilidade.on('change', () => { setQuickButtonState(); applyFilters(); });
    if (typeof tsDestaque !== 'undefined' && tsDestaque) tsDestaque.on('change', () => { setQuickButtonState(); applyFilters(); });
    if (typeof tsSort !== 'undefined' && tsSort) tsSort.on('change', () => {
        setQuickButtonState();
        if (!acervoSection?.classList.contains('hidden')) applyFilters();
    });

    if (clearBtn) clearBtn.addEventListener('click', clearAll);
    if (clearBtn2) clearBtn2.addEventListener('click', clearAll);
    if (backDashboardBtn) {
        backDashboardBtn.addEventListener('click', () => {
            clearAll();
            toggleAcervoView(false);
            dashboardHome?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    applyFilters();

    if (new URLSearchParams(window.location.search).has('acervo')) {
        toggleAcervoView(true);
    }

    // Listeners para os botões "Ver mais" que mostram a seção de acervo
    document.querySelectorAll('a[href="#acervo-section"]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            toggleAcervoView(true);
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = this.closest('.form-delete');
            Swal.fire({ title: 'Excluir registro?', text: 'Esta ação não pode ser desfeita.', icon: 'warning', showCancelButton: true, background: '#0d1420', color: '#fff', confirmButtonColor: '#ef4444', cancelButtonColor: '#1e293b', confirmButtonText: 'Excluir', cancelButtonText: 'Cancelar', customClass: { popup: 'border border-white/10 rounded-xl' } }).then(r => { if (r.isConfirmed) form.submit(); });
        });
    });
});
