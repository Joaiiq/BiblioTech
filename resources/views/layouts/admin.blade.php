<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Administração - Biblioteca</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&family=Merriweather:wght@400;700;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>.swal2-container { z-index: 11050 !important; }</style>
    </head>
    <body class="bg-gray-100 dark:bg-gray-900" style="font-family: 'Inter', sans-serif;">
        <div class="flex">
            <x-admin-sidebar />

            <div class="flex-1 h-screen overflow-y-auto">
                <header class="bg-white dark:bg-gray-800 shadow px-6 py-4 flex justify-between items-center">
                    <h1 class="text-xl font-bold dark:text-white font-serif">Painel Administrativo</h1>
                    @php
                        $adminNotifiable = Auth::guard('web')->check() ? Auth::guard('web')->user() : null;
                        $adminUnreadCount = $adminNotifiable ? $adminNotifiable->unreadNotifications()->count() : 0;
                    @endphp
                    <div class="flex items-center gap-4">
                        @if($adminNotifiable)
                            <button type="button" id="admin-notifications-toggle" class="relative inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/10 transition" aria-controls="admin-notifications-sidebar" aria-label="Notificações">
                                <i class="ph ph-bell text-sm"></i>
                                @if($adminUnreadCount)
                                    <span id="admin-notifications-badge" class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-black text-white bg-red-600 rounded-full">{{ $adminUnreadCount > 9 ? '9+' : $adminUnreadCount }}</span>
                                @endif
                            </button>
                        @endif
                        <div class="text-sm dark:text-gray-300">
                            <div class="flex items-center gap-2">
                                <x-user-avatar :user="Auth::guard('web')->user()" size="h-8 w-8" text="text-[9px]" />
                                <span>Logado como: <strong>{{ Auth::guard('web')->user()?->name ?? 'Equipe' }}</strong></span>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="p-6">
                    @yield('content')
                </main>
            </div>
        </div>

        @if($adminNotifiable)
            <div id="admin-notifications-backdrop" class="fixed inset-0 z-[11020] bg-slate-950/60 opacity-0 pointer-events-none transition-opacity duration-200" aria-hidden="true"></div>
            <aside id="admin-notifications-sidebar" class="fixed top-0 right-[-420px] z-[11030] flex h-screen w-[380px] max-w-[90vw] flex-col border-l border-white/10 bg-[#0d1420] shadow-2xl transition-[right] duration-200" role="dialog" aria-modal="true" aria-label="Notificações">
                <div class="p-5 border-b border-white/10 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">Notificações</h3>
                        <p class="text-[11px] text-gray-400">Avisos do sistema</p>
                    </div>
                    <button type="button" id="admin-notifications-close" class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 text-gray-300 hover:text-white hover:bg-white/10 transition" aria-label="Fechar">
                        <i class="ph ph-x text-sm"></i>
                    </button>
                </div>
                <div class="p-4 overflow-y-auto flex-1 space-y-3">
                    @php
                        $adminUnreads = $adminNotifiable->unreadNotifications()->latest()->get();
                        $adminReads = $adminNotifiable->readNotifications()->latest()->take(30)->get();
                    @endphp

                    @if($adminUnreads->isEmpty() && $adminReads->isEmpty())
                        <div class="text-center py-6 text-gray-400 text-sm">Sem notificações por enquanto.</div>
                    @endif

                    @foreach($adminUnreads as $n)
                        <div class="admin-notification-unread p-3 rounded-md bg-slate-50 dark:bg-white/5 border border-slate-700/20">
                            <div class="flex items-start justify-between">
                                <div class="text-sm text-white">{!! $n->data['message'] ?? ($n->data['title'] ?? 'Notificação') !!}</div>
                                <div class="text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach

                    @foreach($adminReads as $n)
                        <div class="admin-notification-read p-3 rounded-md bg-transparent border border-white/5 text-slate-400">
                            <div class="flex items-start justify-between">
                                <div class="text-sm">{!! $n->data['message'] ?? ($n->data['title'] ?? 'Notificação') !!}</div>
                                <div class="text-xs">{{ $n->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="p-4 border-t border-white/10">
                    <button id="admin-mark-all-read" class="w-full inline-flex items-center justify-center gap-2 h-10 rounded-lg bg-white/5 border border-white/10 text-gray-200 hover:text-white hover:bg-white/10 transition text-[11px] font-bold uppercase tracking-widest">
                        Marcar todas como lidas
                    </button>
                </div>
            </aside>
        @endif

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            const adminNotifToggle = document.getElementById('admin-notifications-toggle');
            const adminNotifSidebar = document.getElementById('admin-notifications-sidebar');
            const adminNotifBackdrop = document.getElementById('admin-notifications-backdrop');
            const adminNotifClose = document.getElementById('admin-notifications-close');
            const adminMarkAll = document.getElementById('admin-mark-all-read');
            const adminBadge = document.getElementById('admin-notifications-badge');

            function setAdminNotificationsOpen(isOpen) {
                if (!adminNotifSidebar || !adminNotifBackdrop || !adminNotifToggle) return;
                adminNotifSidebar.classList.toggle('right-0', isOpen);
                adminNotifSidebar.classList.toggle('right-[-420px]', !isOpen);
                adminNotifBackdrop.classList.toggle('opacity-100', isOpen);
                adminNotifBackdrop.classList.toggle('pointer-events-auto', isOpen);
                adminNotifBackdrop.classList.toggle('pointer-events-none', !isOpen);
                document.body.classList.toggle('overflow-hidden', isOpen);
            }

            adminNotifToggle?.addEventListener('click', () => setAdminNotificationsOpen(true));
            adminNotifClose?.addEventListener('click', () => setAdminNotificationsOpen(false));
            adminNotifBackdrop?.addEventListener('click', () => setAdminNotificationsOpen(false));

            if (adminBadge && typeof Swal !== 'undefined') {
                const initialAdminCount = Number(adminBadge.textContent.replace('+', '')) || 1;
                Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:3500,timerProgressBar:true,background:'#0d1420',color:'#fff'}).fire({
                    icon:'info',
                    title: initialAdminCount === 1 ? '1 pendência administrativa nova' : `${adminBadge.textContent} pendências administrativas`
                });
            }

            if (adminNotifToggle) {
                let knownAdminUnread = Number(adminBadge?.textContent.replace('+', '')) || 0;
                const adminNotificationToast = typeof Swal !== 'undefined'
                    ? Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:3800,timerProgressBar:true,background:'#0d1420',color:'#fff'})
                    : null;

                const updateAdminBadge = (count) => {
                    if (!adminNotifToggle) return;
                    let badge = document.getElementById('admin-notifications-badge');

                    if (count <= 0) {
                        badge?.remove();
                        return;
                    }

                    if (!badge) {
                        badge = document.createElement('span');
                        badge.id = 'admin-notifications-badge';
                        badge.className = 'absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-[10px] font-black text-white bg-red-600 rounded-full';
                        adminNotifToggle.appendChild(badge);
                    }

                    badge.textContent = count > 9 ? '9+' : count;
                };

                setInterval(() => {
                    fetch('/notifications', { headers: { 'Accept': 'application/json' } })
                        .then((response) => response.ok ? response.json() : null)
                        .then((payload) => {
                            if (!payload) return;
                            const unread = Number(payload.unread_count || 0);
                            updateAdminBadge(unread);

                            if (unread > knownAdminUnread && adminNotificationToast) {
                                adminNotificationToast.fire({
                                    icon: 'info',
                                    title: unread - knownAdminUnread === 1 ? 'Nova pendência no painel' : `${unread - knownAdminUnread} novas pendências no painel`,
                                });
                            }

                            knownAdminUnread = unread;
                        })
                        .catch(() => {});
                }, 45000);
            }

            adminMarkAll?.addEventListener('click', function () {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('/notifications/mark-read', { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(() => {
                        setAdminNotificationsOpen(false);
                        adminBadge?.remove();
                        const currentAdminBadge = document.getElementById('admin-notifications-badge');
                        currentAdminBadge?.remove();
                        document.querySelectorAll('.admin-notification-unread').forEach(el => {
                            el.className = 'admin-notification-read p-3 rounded-md bg-transparent border border-white/5 text-slate-400';
                        });
                        if (typeof Swal !== 'undefined') {
                            Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2500,timerProgressBar:true,background:'#0d1420',color:'#fff'}).fire({icon:'success',title:'Notificações marcadas como lidas'});
                        }
                    });
            });

            (function () {
                if (typeof Swal === 'undefined') return;

                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    background: '#0d1420',
                    color: '#fff'
                });

                const applyFieldError = (fieldName) => {
                    const safeName = String(fieldName).replace(/"/g, '\\"');
                    const selector = `[name="${safeName}"], [name="${safeName}[]"]`;
                    const field = document.querySelector(selector);
                    if (!field) return;

                    field.setAttribute('aria-invalid', 'true');
                    field.style.borderColor = '#ef4444';
                    field.style.boxShadow = '0 0 0 1px #ef4444';
                };

                const errors = {!! json_encode($errors->getMessages()) !!};
                Object.entries(errors).forEach(([field, messages]) => {
                    applyFieldError(field);
                    (messages || []).forEach((message) => toast.fire({ icon: 'error', title: message }));
                });

                @if(session('sucesso'))
                    toast.fire({ icon: 'success', title: {!! json_encode(session('sucesso')) !!} });
                @endif
                @if(session('error'))
                    toast.fire({ icon: 'error', title: {!! json_encode(session('error')) !!} });
                @endif
                @if(session('status'))
                    toast.fire({ icon: 'info', title: {!! json_encode(session('status')) !!} });
                @endif
            })();
        </script>
    </body>
</html>
