<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: $persist(true) }" x-effect="document.documentElement.classList.toggle('dark', dark)">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Entrar - BiblioTech</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Merriweather:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-100 via-blue-50 to-slate-100 font-sans text-slate-900 antialiased selection:bg-[#F59E0B] selection:text-slate-950 dark:from-[#0f172a] dark:via-[#0f172a] dark:to-[#0b1120] dark:text-slate-100">
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="login-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                    <circle cx="1" cy="1" r="1" fill="#1E3A8A" opacity="0.08"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#login-dots)"/>
        </svg>
        <i class="ph ph-book-open absolute left-[7%] top-[12%] text-[46px] text-amber-500/20 dark:text-amber-300/10"></i>
        <i class="ph ph-library absolute right-[10%] top-[18%] text-[44px] text-blue-800/10 dark:text-blue-300/10"></i>
        <i class="ph ph-bookmarks absolute right-[18%] bottom-[14%] text-[52px] text-amber-500/20 dark:text-amber-300/10"></i>
    </div>

    <main class="relative z-10 grid min-h-screen grid-cols-1 lg:grid-cols-[minmax(0,0.95fr)_500px]">
        <section class="flex min-h-[36vh] flex-col justify-between p-6 sm:p-10 lg:min-h-screen">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <span class="text-[13px] font-black uppercase leading-tight tracking-tight">
                        <span class="text-[#1E3A8A] dark:text-blue-300">Biblio</span><br>
                        <span class="text-[#F59E0B]">Tech</span>
                    </span>
                </a>

                <button type="button" @click="dark = !dark" class="h-10 w-10 rounded-md border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10" aria-label="Alternar tema">
                    <i class="ph ph-sun text-sm hidden dark:inline-block"></i>
                    <i class="ph ph-moon text-sm dark:hidden"></i>
                </button>
            </div>

            <div class="max-w-xl py-10 lg:py-0">
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Bem-vindo de volta</p>
                <h1 class="mt-3 font-serif text-4xl font-black leading-tight text-slate-950 dark:text-white sm:text-5xl">
                    Sua biblioteca organizada em um só lugar.
                </h1>
                <p class="mt-4 max-w-xl text-sm leading-6 text-slate-600 dark:text-slate-400 sm:text-base">
                    Entre para consultar seus empréstimos, acompanhar reservas ou continuar a rotina de atendimento da biblioteca.
                </p>
            </div>

            <a href="{{ url('/') }}" class="inline-flex w-fit items-center gap-2 rounded-md border border-slate-200 bg-white px-4 py-2 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                <i class="ph ph-arrow-left"></i>
                Voltar ao site
            </a>
        </section>

        <section class="flex items-center justify-center border-t border-slate-200 bg-white/80 p-6 backdrop-blur dark:border-white/10 dark:bg-[#0d1420]/80 lg:border-l lg:border-t-0">
            <div class="w-full max-w-md">
                <div class="rounded-md border border-slate-200 bg-white p-6 shadow-xl shadow-slate-950/10 dark:border-white/10 dark:bg-[#0d1420] dark:shadow-black/30 sm:p-8">
                    <div class="mb-7">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Acesso</p>
                        <h2 class="mt-1 font-serif text-3xl font-black text-slate-950 dark:text-white">Entrar</h2>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Use o e-mail cadastrado na biblioteca.</p>
                    </div>

                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">E-mail</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-100">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div x-data="{ showPassword: false }">
                            <label for="password" class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Senha</label>
                            <div class="relative">
                                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" class="h-11 w-full rounded-md border border-slate-200 bg-white px-3 pr-12 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-100">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white" :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'">
                                    <i class="ph text-lg" :class="showPassword ? 'ph-eye-slash' : 'ph-eye'"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
                                <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1E3A8A] dark:border-white/10 dark:bg-[#080d14]">
                                <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Lembrar de mim</span>
                            </label>
                        </div>

                        <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                            <i class="ph ph-sign-in"></i>
                            Acessar
                        </button>
                    </form>

                    <div class="{{ session('login_failed_attempts', 0) > 2 ? '' : 'hidden' }} mt-6 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 dark:border-white/10 dark:bg-white/[.03] dark:text-slate-400">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                                <i class="ph ph-key"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">Problemas para entrar?</p>
                                <p class="mt-1 text-xs leading-5">Envie um pedido para a equipe redefinir sua senha pelo painel administrativo.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('login.support') }}" class="mt-4 space-y-3">
                            @csrf
                            <div>
                                <label for="support_email" class="sr-only">E-mail cadastrado</label>
                                <input id="support_email" type="email" name="email" value="{{ old('email') }}" required placeholder="Seu e-mail cadastrado" class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-100">
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div>
                                <label for="support_message" class="sr-only">Observação</label>
                                <textarea id="support_message" name="message" rows="2" maxlength="500" placeholder="Observação opcional" class="w-full resize-none rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-[#1E3A8A] focus:ring-2 focus:ring-[#1E3A8A]/20 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-100">{{ old('message') }}</textarea>
                                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                            </div>
                            <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md border border-amber-500 bg-amber-500 px-4 text-[11px] font-black uppercase tracking-widest text-slate-950 transition hover:bg-amber-400">
                                <i class="ph ph-paper-plane-tilt"></i>
                                Pedir ajuda
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

            const errors = {!! json_encode($errors->all()) !!};
            errors.forEach((message) => toast.fire({ icon: 'error', title: message }));

            @if(session('sucesso'))
                toast.fire({ icon: 'success', title: {!! json_encode(session('sucesso')) !!} });
            @endif

            @if(session('error'))
                toast.fire({ icon: 'error', title: {!! json_encode(session('error')) !!} });
            @endif

            @if(session('status'))
                toast.fire({ icon: 'info', title: {!! json_encode(session('status')) !!} });
            @endif
        });
    </script>
</body>
</html>
