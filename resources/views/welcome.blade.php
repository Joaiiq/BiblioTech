<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BiblioTech — Gestão inteligente de bibliotecas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Merriweather:wght@700;900&display=swap" rel="stylesheet">

    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
    <script src="https://unpkg.com/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
</head>

<body class="overflow-x-hidden bg-slate-950 font-['Inter'] text-white antialiased">
    <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_15%_20%,rgba(245,158,11,0.22),transparent_28%),radial-gradient(circle_at_80%_10%,rgba(59,130,246,0.24),transparent_30%),radial-gradient(circle_at_50%_80%,rgba(14,165,233,0.15),transparent_34%),linear-gradient(135deg,#020617_0%,#0f172a_45%,#111827_100%)]">

        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.045)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.045)_1px,transparent_1px)] bg-[size:44px_44px] opacity-60 [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>

        <div class="pointer-events-none absolute -left-32 top-40 h-96 w-96 rounded-full bg-amber-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-32 top-24 h-96 w-96 rounded-full bg-blue-500/20 blur-3xl"></div>

        <header class="relative z-20">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
                <a href="{{ url('/') }}" class="hero-logo flex items-center">
                    <div class="text-[13px] font-black tracking-tight leading-tight">
                        <span class="text-blue-300">BIBLIO</span><br>
                        <span class="text-[#F59E0B]">TECH</span>
                    </div>
                </a>

                <div class="hidden items-center gap-8 text-sm font-semibold text-slate-300 md:flex">
                    <a href="#livros" class="transition hover:text-amber-300">Livros</a>
                    <a href="#recursos" class="transition hover:text-amber-300">Recursos</a>
                    <a href="#fluxo" class="transition hover:text-amber-300">Fluxo</a>
                    <a href="#controle" class="transition hover:text-amber-300">Controle</a>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="rounded-full bg-amber-400 px-5 py-2.5 text-sm font-black text-slate-950 shadow-lg shadow-amber-500/20 transition hover:bg-amber-300">
                        Entrar
                    </a>
                </div>
            </nav>
        </header>

        <main class="relative z-10">
            <section class="mx-auto grid max-w-7xl items-center gap-14 px-6 pb-24 pt-12 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:pb-32 lg:pt-20">
                <div>
                    <div class="hero-badge mb-6 inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-2 text-sm font-bold text-amber-200 backdrop-blur-xl">
                        <span class="h-2 w-2 rounded-full bg-amber-300"></span>
                        Sistema completo para biblioteca
                    </div>

                    <h1 class="hero-title max-w-4xl font-['Merriweather'] text-5xl font-black leading-[1.03] tracking-tight text-white md:text-7xl">
                        Gestão de biblioteca com controle real, não só cadastro.
                    </h1>

                    <p class="hero-subtitle mt-6 max-w-2xl text-lg leading-8 text-slate-300 md:text-xl">
                        O BiblioTech centraliza acervo, empréstimos, reservas, membros, multas, carteirinhas e relatórios em uma experiência visual limpa e objetiva.
                    </p>

                    <div class="hero-typed mt-7 h-8 text-base font-bold text-amber-200 md:text-lg">
                        <span id="typed-text"></span>
                    </div>

                    <div class="hero-actions mt-9 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('login') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-400 px-7 py-4 text-base font-black text-slate-950 shadow-xl shadow-amber-500/20 transition hover:-translate-y-1 hover:bg-amber-300">
                            Entrar no sistema
                            <i class="ph ph-arrow-right text-xl transition group-hover:translate-x-1"></i>
                        </a>

                        <a href="#recursos" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-7 py-4 text-base font-bold text-white backdrop-blur-xl transition hover:-translate-y-1 hover:bg-white/10">
                            Ver recursos
                            <i class="ph ph-squares-four text-xl"></i>
                        </a>
                    </div>

                    <div class="hero-stats mt-10 grid max-w-2xl grid-cols-3 gap-3">
                        <div class="rounded-2xl border border-white/10 bg-white/[.04] p-4 backdrop-blur-xl">
                            <p class="text-2xl font-black text-white">360°</p>
                            <p class="mt-1 text-xs font-medium text-slate-400">gestão do acervo</p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/[.04] p-4 backdrop-blur-xl">
                            <p class="text-2xl font-black text-white">PDF</p>
                            <p class="mt-1 text-xs font-medium text-slate-400">relatórios e carteirinha</p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/[.04] p-4 backdrop-blur-xl">
                            <p class="text-2xl font-black text-white">Logs</p>
                            <p class="mt-1 text-xs font-medium text-slate-400">auditoria interna</p>
                        </div>
                    </div>
                </div>

                <div class="hero-panel relative">
                    <div class="panel-glow absolute -left-8 top-8 h-44 w-44 rounded-full bg-amber-400/20 blur-3xl"></div>
                    <div class="panel-glow absolute -right-8 bottom-8 h-52 w-52 rounded-full bg-blue-500/20 blur-3xl"></div>

                    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/70 p-5 shadow-2xl shadow-black/40 backdrop-blur-2xl">
                        <div class="rounded-[1.5rem] bg-slate-950/80 p-5 ring-1 ring-white/10">
                            <div class="mb-5 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-400">Painel BiblioTech</p>
                                    <p class="text-2xl font-black text-white">Operação diária</p>
                                </div>

                                <div class="rounded-2xl bg-emerald-400/10 px-3 py-2 text-sm font-bold text-emerald-300">
                                    Online
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="dashboard-card rounded-3xl bg-gradient-to-br from-amber-300 to-amber-500 p-5 text-slate-950">
                                    <i class="ph ph-book-bookmark mb-8 block text-4xl"></i>
                                    <p class="text-sm font-bold opacity-80">Livros cadastrados</p>
                                    <p class="mt-1 text-4xl font-black">{{ number_format($landingStats['livros'] ?? 0, 0, ',', '.') }}</p>
                                </div>

                                <div class="dashboard-card rounded-3xl border border-white/10 bg-white/[.04] p-5">
                                    <i class="ph ph-users-three mb-8 block text-4xl text-blue-300"></i>
                                    <p class="text-sm font-bold text-slate-400">Membros ativos</p>
                                    <p class="mt-1 text-4xl font-black">{{ number_format($landingStats['membros'] ?? 0, 0, ',', '.') }}</p>
                                </div>

                                <div class="dashboard-card rounded-3xl border border-white/10 bg-white/[.04] p-5">
                                    <i class="ph ph-calendar-check mb-8 block text-4xl text-emerald-300"></i>
                                    <p class="text-sm font-bold text-slate-400">Empréstimos</p>
                                    <p class="mt-1 text-4xl font-black">{{ number_format($landingStats['emprestimos'] ?? 0, 0, ',', '.') }}</p>
                                </div>

                                <div class="dashboard-card rounded-3xl border border-white/10 bg-white/[.04] p-5">
                                    <i class="ph ph-warning-circle mb-8 block text-4xl text-red-300"></i>
                                    <p class="text-sm font-bold text-slate-400">Pendências</p>
                                    <p class="mt-1 text-4xl font-black">{{ str_pad((string) ($landingStats['pendencias'] ?? 0), 2, '0', STR_PAD_LEFT) }}</p>
                                </div>
                            </div>

                            <div class="mt-4 rounded-3xl border border-white/10 bg-white/[.04] p-5">
                                <div class="mb-4 flex items-center justify-between">
                                    <p class="font-black">Fila de ações</p>
                                    <p class="text-sm text-slate-400">tempo real</p>
                                </div>

                                <div class="space-y-3">
                                    <div class="queue-item flex items-center justify-between rounded-2xl bg-slate-900/80 p-3">
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-400/10 text-blue-300">
                                                <i class="ph ph-bookmark-simple text-xl"></i>
                                            </span>

                                            <div>
                                                <p class="text-sm font-bold">Reserva solicitada</p>
                                                <p class="text-xs text-slate-400">Membro aguardando aprovação</p>
                                            </div>
                                        </div>

                                        <span class="text-xs font-bold text-amber-300">novo</span>
                                    </div>

                                    <div class="queue-item flex items-center justify-between rounded-2xl bg-slate-900/80 p-3">
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-red-400/10 text-red-300">
                                                <i class="ph ph-receipt text-xl"></i>
                                            </span>

                                            <div>
                                                <p class="text-sm font-bold">Multa em aberto</p>
                                                <p class="text-xs text-slate-400">Atraso calculado automaticamente</p>
                                            </div>
                                        </div>

                                        <span class="text-xs font-bold text-red-300">atenção</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="floating-card absolute -left-4 -top-5 hidden rounded-2xl border border-white/10 bg-white/10 p-4 shadow-xl backdrop-blur-xl lg:block">
                        <p class="text-sm font-bold text-slate-200">Carteirinha digital</p>
                        <p class="text-xs text-slate-400">identificação em PDF</p>
                    </div>

                    <div class="floating-card absolute -bottom-6 -right-3 hidden rounded-2xl border border-amber-300/20 bg-amber-300/10 p-4 shadow-xl backdrop-blur-xl lg:block">
                        <p class="text-sm font-bold text-amber-200">Relatórios prontos</p>
                        <p class="text-xs text-amber-100/70">multas, auditoria e acervo</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    @if(($landingBooks ?? collect())->isNotEmpty())
        <section id="livros" class="bg-slate-950 px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="grid gap-8 lg:grid-cols-[.75fr_1.25fr] lg:items-end">
                    <div data-aos="fade-up">
                        <p class="mb-3 text-sm font-black uppercase tracking-[0.3em] text-amber-300">Vitrine do acervo</p>
                        <h2 class="font-['Merriweather'] text-4xl font-black tracking-tight text-white md:text-5xl">
                            Algumas obras para dar vontade de entrar.
                        </h2>
                        <p class="mt-5 text-lg leading-8 text-slate-400">
                            Uma amostra visual do acervo para apresentar o sistema sem liberar ações fora do login.
                        </p>
                    </div>

                    <div data-aos="fade-up" class="overflow-hidden">
                        <div class="swiper landing-books-swiper">
                            <div class="swiper-wrapper">
                                @foreach($landingBooks as $livro)
                                    <article class="swiper-slide overflow-hidden rounded-3xl border border-white/10 bg-white/[.04] shadow-xl shadow-black/20">
                                        <div class="relative aspect-[3/4] overflow-hidden bg-slate-900">
                                            @if($livro->capa)
                                                <img src="{{ asset('storage/' . $livro->capa) }}" alt="{{ $livro->titulo }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-slate-800 to-slate-950 text-slate-500">
                                                    <i class="ph ph-book-open-text text-5xl"></i>
                                                    <span class="mt-3 text-xs font-black uppercase tracking-widest">Sem capa</span>
                                                </div>
                                            @endif

                                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-transparent p-4">
                                                <span class="inline-flex rounded-full bg-amber-400 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-slate-950">
                                                    {{ $livro->categoriasTexto() }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="p-4">
                                            <h3 class="line-clamp-2 font-['Merriweather'] text-lg font-black leading-tight text-white">
                                                {{ $livro->titulo }}
                                            </h3>
                                            <p class="mt-1 truncate text-sm font-semibold text-slate-400">{{ $livro->autor?->nome ?? 'Autor não informado' }}</p>
                                            <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-500">
                                                {{ $livro->sinopse ?: 'Obra disponível no acervo da biblioteca.' }}
                                            </p>
                                            <p class="mt-4 text-xs font-black uppercase tracking-widest text-emerald-300">{{ (int) $livro->quantidade }} exemplar{{ (int) $livro->quantidade === 1 ? '' : 'es' }} no acervo</p>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                            <div class="mt-5 flex items-center justify-end gap-2">
                                <button type="button" class="landing-books-prev grid h-10 w-10 place-items-center rounded-full border border-white/10 bg-white/[.04] text-slate-300 transition hover:border-amber-300/40 hover:text-amber-300" aria-label="Livro anterior">
                                    <i class="ph ph-caret-left"></i>
                                </button>
                                <button type="button" class="landing-books-next grid h-10 w-10 place-items-center rounded-full border border-white/10 bg-white/[.04] text-slate-300 transition hover:border-amber-300/40 hover:text-amber-300" aria-label="Próximo livro">
                                    <i class="ph ph-caret-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section id="recursos" class="bg-slate-950 px-6 py-24 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="max-w-3xl" data-aos="fade-up">
                <p class="mb-3 text-sm font-black uppercase tracking-[0.3em] text-amber-300">Recursos principais</p>
                <h2 class="font-['Merriweather'] text-4xl font-black tracking-tight text-white md:text-5xl">
                    O valor do projeto aparece quando o fluxo inteiro fica claro.
                </h2>
                <p class="mt-5 text-lg leading-8 text-slate-400">
                    Cada módulo foi pensado para reduzir retrabalho: catálogo organizado, atendimento mais rápido e acompanhamento claro para equipe e membros.
                </p>
            </div>

            <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <div data-aos="fade-up" data-aos-delay="0" class="rounded-3xl border border-white/10 bg-white/[.035] p-7 transition hover:-translate-y-1 hover:bg-white/[.06]">
                    <i class="ph ph-books block text-5xl text-amber-300"></i>
                    <h3 class="mt-6 text-xl font-black">Acervo organizado</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-400">
                        Cadastro e visualização de livros com capa, autor, categoria, descrição e disponibilidade.
                    </p>
                </div>

                <div data-aos="fade-up" data-aos-delay="80" class="rounded-3xl border border-white/10 bg-white/[.035] p-7 transition hover:-translate-y-1 hover:bg-white/[.06]">
                    <i class="ph ph-arrows-clockwise block text-5xl text-blue-300"></i>
                    <h3 class="mt-6 text-xl font-black">Empréstimos e devoluções</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-400">
                        Controle de retirada, prazo, renovação, devolução e situação do empréstimo.
                    </p>
                </div>

                <div data-aos="fade-up" data-aos-delay="160" class="rounded-3xl border border-white/10 bg-white/[.035] p-7 transition hover:-translate-y-1 hover:bg-white/[.06]">
                    <i class="ph ph-bookmarks-simple block text-5xl text-emerald-300"></i>
                    <h3 class="mt-6 text-xl font-black">Reservas e favoritos</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-400">
                        O membro acompanha livros de interesse e solicita reservas diretamente pelo sistema.
                    </p>
                </div>

                <div data-aos="fade-up" data-aos-delay="0" class="rounded-3xl border border-white/10 bg-white/[.035] p-7 transition hover:-translate-y-1 hover:bg-white/[.06]">
                    <i class="ph ph-money-wavy block text-5xl text-red-300"></i>
                    <h3 class="mt-6 text-xl font-black">Multas por atraso</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-400">
                        Pendências ficam visíveis e podem ser acompanhadas sem depender de controle manual.
                    </p>
                </div>

                <div data-aos="fade-up" data-aos-delay="80" class="rounded-3xl border border-white/10 bg-white/[.035] p-7 transition hover:-translate-y-1 hover:bg-white/[.06]">
                    <i class="ph ph-identification-card block text-5xl text-purple-300"></i>
                    <h3 class="mt-6 text-xl font-black">Carteirinha digital</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-400">
                        Identificação do membro com página própria e exportação em PDF.
                    </p>
                </div>

                <div data-aos="fade-up" data-aos-delay="160" class="rounded-3xl border border-white/10 bg-white/[.035] p-7 transition hover:-translate-y-1 hover:bg-white/[.06]">
                    <i class="ph ph-chart-bar block text-5xl text-cyan-300"></i>
                    <h3 class="mt-6 text-xl font-black">Relatórios e auditoria</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-400">
                        Dados de operação, histórico e relatórios para demonstrar controle administrativo.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="fluxo" class="bg-slate-900 px-6 py-24 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-12 lg:grid-cols-[.9fr_1.1fr] lg:items-center">
                <div data-aos="fade-right">
                    <p class="mb-3 text-sm font-black uppercase tracking-[0.3em] text-amber-300">Fluxo do sistema</p>
                    <h2 class="font-['Merriweather'] text-4xl font-black tracking-tight text-white md:text-5xl">
                        Do cadastro até o relatório final.
                    </h2>
                    <p class="mt-5 text-lg leading-8 text-slate-400">
                        A operação segue uma linha simples: registrar, solicitar, acompanhar, devolver e consultar indicadores quando a gestão precisar.
                    </p>
                </div>

                <div class="grid gap-4" data-aos="fade-left">
                    <div class="flow-card flex gap-4 rounded-3xl border border-white/10 bg-slate-950/70 p-5">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-amber-400 text-lg font-black text-slate-950">1</div>
                        <div>
                            <h3 class="font-black">Bibliotecário cadastra o acervo</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-400">Livros entram com dados, categoria, autor, capa e quantidade disponível.</p>
                        </div>
                    </div>

                    <div class="flow-card flex gap-4 rounded-3xl border border-white/10 bg-slate-950/70 p-5">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-amber-400 text-lg font-black text-slate-950">2</div>
                        <div>
                            <h3 class="font-black">Membro consulta e solicita</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-400">O usuário encontra livros, favorita, reserva ou solicita empréstimo.</p>
                        </div>
                    </div>

                    <div class="flow-card flex gap-4 rounded-3xl border border-white/10 bg-slate-950/70 p-5">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-amber-400 text-lg font-black text-slate-950">3</div>
                        <div>
                            <h3 class="font-black">Equipe aprova e acompanha</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-400">O sistema acompanha retirada, uso, renovação, devolução e atrasos.</p>
                        </div>
                    </div>

                    <div class="flow-card flex gap-4 rounded-3xl border border-white/10 bg-slate-950/70 p-5">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-amber-400 text-lg font-black text-slate-950">4</div>
                        <div>
                            <h3 class="font-black">Gestão consulta relatórios</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-400">Relatórios, auditoria e exportações ajudam a provar o controle do sistema.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="controle" class="bg-slate-950 px-6 py-24 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div data-aos="zoom-in-up" class="overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-slate-900 to-slate-950 p-8 shadow-2xl shadow-black/30 md:p-12">
                <div class="grid gap-10 lg:grid-cols-[1fr_.8fr] lg:items-center">
                    <div>
                        <p class="mb-3 text-sm font-black uppercase tracking-[0.3em] text-amber-300">Controle diário</p>
                        <h2 class="font-['Merriweather'] text-4xl font-black tracking-tight text-white md:text-5xl">
                            Uma entrada direta para quem usa a biblioteca todos os dias.
                        </h2>
                        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-400">
                            A tela inicial apresenta o que importa: acervo, circulação, reservas, multas e relatórios. O acesso fica centralizado no login da instituição.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-amber-300/20 bg-amber-300/10 p-6">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-sign-in text-4xl text-amber-300"></i>
                            <h3 class="text-xl font-black text-white">Acesso controlado</h3>
                        </div>

                        <p class="mt-4 text-sm leading-7 text-amber-100/80">
                            Contas de membros e equipe entram pelo mesmo login, com permissões separadas para manter a rotina da biblioteca segura.
                        </p>

                        <div class="mt-6">
                            <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-amber-400 px-6 py-4 font-black text-slate-950 transition hover:bg-amber-300">
                                Entrar no BiblioTech
                                <i class="ph ph-arrow-right text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            AOS.init({
                duration: 850,
                easing: 'ease-out-cubic',
                once: true,
                offset: 80
            });

            new Typed('#typed-text', {
                strings: [
                    'Controle empréstimos sem planilha manual.',
                    'Organize membros, reservas e multas.',
                    'Gere carteirinhas e relatórios em PDF.',
                    'Mostre gestão real, não só cadastro.'
                ],
                typeSpeed: 42,
                backSpeed: 24,
                backDelay: 1800,
                loop: true,
                showCursor: true,
                cursorChar: '|'
            });

            gsap.from('.hero-logo', {
                y: -18,
                opacity: 0,
                duration: 0.7,
                ease: 'power3.out'
            });

            gsap.from('.hero-badge', {
                y: 24,
                opacity: 0,
                duration: 0.7,
                delay: 0.1,
                ease: 'power3.out'
            });

            gsap.from('.hero-title', {
                y: 34,
                opacity: 0,
                duration: 0.9,
                delay: 0.2,
                ease: 'power3.out'
            });

            gsap.from('.hero-subtitle', {
                y: 28,
                opacity: 0,
                duration: 0.8,
                delay: 0.35,
                ease: 'power3.out'
            });

            gsap.from('.hero-typed', {
                y: 20,
                opacity: 0,
                duration: 0.7,
                delay: 0.45,
                ease: 'power3.out'
            });

            gsap.from('.hero-actions', {
                y: 24,
                opacity: 0,
                duration: 0.7,
                delay: 0.55,
                ease: 'power3.out'
            });

            gsap.from('.hero-stats > div', {
                y: 24,
                opacity: 0,
                duration: 0.7,
                delay: 0.65,
                stagger: 0.08,
                ease: 'power3.out'
            });

            gsap.from('.hero-panel', {
                x: 44,
                opacity: 0,
                duration: 1,
                delay: 0.35,
                ease: 'power3.out'
            });

            gsap.from('.dashboard-card', {
                scale: 0.92,
                opacity: 0,
                duration: 0.75,
                delay: 0.65,
                stagger: 0.08,
                ease: 'back.out(1.7)'
            });

            gsap.from('.queue-item', {
                x: 24,
                opacity: 0,
                duration: 0.65,
                delay: 0.95,
                stagger: 0.1,
                ease: 'power3.out'
            });

            gsap.to('.floating-card', {
                y: -14,
                duration: 2.8,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
                stagger: 0.35
            });

            gsap.to('.panel-glow', {
                scale: 1.15,
                opacity: 0.65,
                duration: 3.5,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
                stagger: 0.45
            });

            gsap.to('.flow-card', {
                y: -4,
                duration: 2.4,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
                stagger: 0.18
            });

            if (document.querySelector('.landing-books-swiper')) {
                new Swiper('.landing-books-swiper', {
                    loop: {{ ($landingBooks ?? collect())->count() > 3 ? 'true' : 'false' }},
                    spaceBetween: 16,
                    autoplay: {
                        delay: 2600,
                        disableOnInteraction: false,
                    },
                    navigation: {
                        nextEl: '.landing-books-next',
                        prevEl: '.landing-books-prev',
                    },
                    breakpoints: {
                        0: { slidesPerView: 1.15 },
                        640: { slidesPerView: 2.1 },
                        1024: { slidesPerView: 3 },
                    },
                });
            }
        });
    </script>
</body>
</html>
