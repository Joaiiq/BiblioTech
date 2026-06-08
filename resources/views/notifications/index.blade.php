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
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Central</p>
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Notificações</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Acompanhe avisos de empréstimos, devoluções e mensagens</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('notifications.clear-read') }}" data-confirm="delete" data-title="Limpar notificações lidas?" data-text="Somente as notificações já lidas serão removidas da central.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                        <i class="ph ph-broom"></i>
                        Limpar lidas
                    </button>
                </form>
                <button id="notifications-mark-page" type="button" class="inline-flex h-10 items-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                    <i class="ph ph-checks"></i>
                    Marcar lidas
                </button>
                <button type="button" @click="dark = !dark" class="h-10 w-10 rounded-md border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10" aria-label="Alternar tema">
                    <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="-mx-4 min-h-screen bg-gradient-to-b from-slate-100 via-blue-50 to-slate-100 px-4 py-8 dark:from-[#0f172a] dark:via-[#0f172a] dark:to-[#0b1120] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <main class="mx-auto max-w-6xl space-y-6">
            <section class="grid grid-cols-2 gap-3 xl:grid-cols-7">
                <button data-notification-filter="todas" class="notification-filter rounded-md border border-blue-200 bg-blue-50 p-4 text-left text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                    <p class="text-[10px] font-black uppercase tracking-widest">Todas</p>
                    <p class="mt-1 text-2xl font-black">{{ $notifications->count() }}</p>
                </button>
                <button data-notification-filter="nao-lidas" class="notification-filter rounded-md border border-red-200 bg-red-50 p-4 text-left text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                    <p class="text-[10px] font-black uppercase tracking-widest">Não lidas</p>
                    <p class="mt-1 text-2xl font-black">{{ $unreadCount }}</p>
                </button>
                <button data-notification-filter="emprestimos" class="notification-filter rounded-md border border-slate-200 bg-white p-4 text-left text-slate-700 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-300">
                    <p class="text-[10px] font-black uppercase tracking-widest">Empréstimos</p>
                    <p class="mt-1 text-2xl font-black">{{ $typeCounts['emprestimos'] ?? 0 }}</p>
                </button>
                <button data-notification-filter="devolucoes" class="notification-filter rounded-md border border-slate-200 bg-white p-4 text-left text-slate-700 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-300">
                    <p class="text-[10px] font-black uppercase tracking-widest">Devoluções</p>
                    <p class="mt-1 text-2xl font-black">{{ $typeCounts['devolucoes'] ?? 0 }}</p>
                </button>
                <button data-notification-filter="reservas" class="notification-filter rounded-md border border-slate-200 bg-white p-4 text-left text-slate-700 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-300">
                    <p class="text-[10px] font-black uppercase tracking-widest">Reservas</p>
                    <p class="mt-1 text-2xl font-black">{{ $typeCounts['reservas'] ?? 0 }}</p>
                </button>
                <button data-notification-filter="alertas" class="notification-filter rounded-md border border-slate-200 bg-white p-4 text-left text-slate-700 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-300">
                    <p class="text-[10px] font-black uppercase tracking-widest">Alertas</p>
                    <p class="mt-1 text-2xl font-black">{{ $typeCounts['alertas'] ?? 0 }}</p>
                </button>
                <button data-notification-filter="financeiro" class="notification-filter rounded-md border border-slate-200 bg-white p-4 text-left text-slate-700 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-300">
                    <p class="text-[10px] font-black uppercase tracking-widest">Financeiro</p>
                    <p class="mt-1 text-2xl font-black">{{ $typeCounts['financeiro'] ?? 0 }}</p>
                </button>
                <button data-notification-filter="mensagens" class="notification-filter rounded-md border border-slate-200 bg-white p-4 text-left text-slate-700 dark:border-white/10 dark:bg-[#0d1420] dark:text-slate-300">
                    <p class="text-[10px] font-black uppercase tracking-widest">Mensagens</p>
                    <p class="mt-1 text-2xl font-black">{{ $typeCounts['mensagens'] ?? 0 }}</p>
                </button>
            </section>

            <section class="rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                <div class="flex flex-col gap-3 border-b border-slate-200 p-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Linha do tempo</p>
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white">Últimos avisos</h2>
                    </div>
                    <div class="relative w-full sm:w-80">
                        <i class="ph ph-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="notification-search" type="text" placeholder="Buscar aviso..." class="h-10 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-200">
                    </div>
                </div>

                <div class="divide-y divide-slate-200 dark:divide-white/10">
                    @forelse($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $meta = $notification->meta;
                            $title = str_replace(['pedido de aluguel', 'aluguel'], ['solicitação de empréstimo', 'empréstimo'], $data['title'] ?? 'Notificação');
                            $message = str_replace(['pedido de aluguel', 'solicitou o aluguel', ' alugar '], ['solicitação de empréstimo', 'solicitou o empréstimo', ' solicitar '], $data['message'] ?? 'Aviso do sistema.');
                            $isUnread = is_null($notification->read_at);
                        @endphp
                        <article class="notification-row p-5 {{ $isUnread ? 'bg-blue-50/60 dark:bg-blue-500/5' : '' }}" data-filter-group="{{ $meta['grupo'] }}" data-unread="{{ $isUnread ? '1' : '0' }}" data-search="{{ \Illuminate\Support\Str::lower($title . ' ' . $message) }}">
                            <div class="grid gap-4 lg:grid-cols-[48px_minmax(0,1fr)_auto] lg:items-start">
                                <span class="flex h-12 w-12 items-center justify-center rounded-md border text-xl {{ $meta['classes'] }}">
                                    <i class="ph {{ $meta['icon'] }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-black text-slate-950 dark:text-white">{{ $title }}</h3>
                                        <span class="rounded-md border px-2 py-0.5 text-[9px] font-black uppercase tracking-widest {{ $meta['classes'] }}">{{ $meta['label'] }}</span>
                                        @if($isUnread)
                                            <span class="rounded-md bg-red-600 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-white">Nova</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{!! nl2br(e($message)) !!}</p>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @if($notification->action)
                                            <a href="{{ $notification->action['url'] }}" class="inline-flex h-9 items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-3 text-[10px] font-black uppercase tracking-widest text-blue-700 transition hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20">
                                                <i class="ph ph-arrow-square-out"></i>
                                                {{ $notification->action['label'] }}
                                            </a>
                                        @endif
                                        @if($isUnread)
                                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                                                    <i class="ph ph-check"></i>
                                                    Marcar lida
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 lg:block lg:text-right">
                                    <time class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                    </time>
                                    @if($notification->read_at)
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 lg:mt-1">Lida</p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-10 text-center">
                            <i class="ph ph-bell-slash mx-auto block text-5xl text-slate-300 dark:text-slate-600"></i>
                            <p class="mt-4 text-sm font-bold text-slate-500 dark:text-slate-400">Sem notificações por enquanto.</p>
                        </div>
                    @endforelse
                </div>

                <div id="notification-empty-filter" class="hidden p-10 text-center text-sm text-slate-500 dark:text-slate-400">
                    Nenhuma notificação encontrada para esse filtro.
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rows = Array.from(document.querySelectorAll('.notification-row'));
            const buttons = Array.from(document.querySelectorAll('.notification-filter'));
            const search = document.getElementById('notification-search');
            const empty = document.getElementById('notification-empty-filter');
            const markButton = document.getElementById('notifications-mark-page');
            let active = 'todas';

            const apply = () => {
                const query = (search?.value || '').toLowerCase();
                let visible = 0;

                rows.forEach((row) => {
                    const matchesType = active === 'todas'
                        || (active === 'nao-lidas' ? row.dataset.unread === '1' : row.dataset.filterGroup === active);
                    const matchesSearch = !query || row.dataset.search.includes(query);
                    const show = matchesType && matchesSearch;
                    row.classList.toggle('hidden', !show);
                    if (show) visible += 1;
                });

                empty?.classList.toggle('hidden', visible > 0);
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    active = button.dataset.notificationFilter || 'todas';
                    buttons.forEach((item) => item.classList.remove('ring-2', 'ring-[#1E3A8A]'));
                    button.classList.add('ring-2', 'ring-[#1E3A8A]');
                    apply();
                });
            });

            search?.addEventListener('input', apply);

            markButton?.addEventListener('click', () => {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('{{ route('notifications.mark-read') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                }).then(() => window.location.reload());
            });

            apply();
        });
    </script>
</x-app-layout>
