<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 flex-col items-center justify-center gap-1">
                    <i class="ph ph-library text-4xl text-[#1E3A8A] dark:text-blue-400"></i>
                    <div class="text-center text-[11px] font-black leading-tight tracking-tight">
                        <span class="text-[#1E3A8A] dark:text-blue-400">BIBLIO</span><br>
                        <span class="text-[#F59E0B]">TECH</span>
                    </div>
                </a>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Atendimento</p>
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Cadastrar membro</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Dados e credenciais em uma ficha única</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.membros.perfis') }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    <i class="ph ph-arrow-left"></i>
                    Voltar
                </a>
                <button type="button" @click="dark = !dark" class="h-10 w-10 rounded-md border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10" aria-label="Alternar tema">
                    <i class="ph text-sm" :class="dark ? 'ph-sun' : 'ph-moon'"></i>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="-mx-4 min-h-screen bg-gradient-to-b from-slate-100 via-blue-50 to-slate-100 px-4 py-8 dark:from-[#0f172a] dark:via-[#0f172a] dark:to-[#0b1120] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <main class="mx-auto grid max-w-7xl items-start gap-5 lg:grid-cols-[360px_minmax(0,1fr)]">
            <aside class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95 lg:sticky lg:top-6">
                <div class="flex items-center gap-3">
                    <span id="preview-initial" class="inline-flex h-12 w-12 items-center justify-center rounded-md bg-[#1E3A8A] text-lg font-black text-white">M</span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Prévia da ficha</p>
                        <h2 id="preview-name" class="truncate font-serif text-2xl font-black text-slate-950 dark:text-white">Novo membro</h2>
                        <p id="preview-email" class="truncate text-xs text-slate-500 dark:text-slate-400">email ainda não informado</p>
                    </div>
                </div>

                <div class="mt-5 rounded-md border border-dashed border-blue-300 bg-blue-50 p-4 dark:border-blue-500/30 dark:bg-blue-500/10">
                    <p class="text-[10px] font-black uppercase tracking-[.16em] text-blue-700 dark:text-blue-300">Carteirinha</p>
                    <p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">Será gerada automaticamente ao salvar.</p>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">CPF</p>
                        <p id="preview-cpf" class="mt-1 truncate text-sm font-bold text-slate-900 dark:text-white">000.000.000-00</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Telefone</p>
                        <p id="preview-phone" class="mt-1 truncate text-sm font-bold text-slate-900 dark:text-white">(00) 00000-0000</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/[.03]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Perfil</p>
                        <p class="mt-1 truncate text-sm font-bold text-slate-900 dark:text-white">Membro</p>
                    </div>
                </div>

                <div class="mt-5 rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-black uppercase tracking-[.16em] text-slate-500">Progresso</p>
                        <span id="preview-progress-label" class="text-xs font-black text-blue-700 dark:text-blue-300">0%</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                        <div id="preview-progress-bar" class="h-full w-0 rounded-full bg-[#1E3A8A] transition-all"></div>
                    </div>
                    <p id="preview-password" class="mt-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Informe uma senha com pelo menos 8 caracteres.</p>
                </div>
            </aside>

            <form id="member-create-form" method="POST" action="{{ route('membros.store') }}" class="grid gap-5" novalidate>
                @csrf

                <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                            <i class="ph ph-user"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Dados pessoais</p>
                            <h2 class="text-lg font-black text-slate-950 dark:text-white">Identificação do membro</h2>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label for="nome" :value="'Nome completo'" />
                            <x-text-input id="nome" name="nome" type="text" value="{{ old('nome') }}" required autofocus data-member-field class="mt-2 block w-full bg-white text-slate-900 dark:bg-[#080d14] dark:text-white" />
                            <x-input-error class="mt-2" :messages="$errors->get('nome')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="'E-mail'" />
                            <x-text-input id="email" name="email" type="email" value="{{ old('email') }}" required data-member-field class="mt-2 block w-full bg-white text-slate-900 dark:bg-[#080d14] dark:text-white" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="cpf" :value="'CPF'" />
                            <x-text-input id="cpf" name="cpf" type="text" value="{{ old('cpf') }}" required data-member-field inputmode="numeric" placeholder="000.000.000-00" class="mt-2 block w-full bg-white text-slate-900 dark:bg-[#080d14] dark:text-white" />
                            <x-input-error class="mt-2" :messages="$errors->get('cpf')" />
                        </div>

                        <div>
                            <x-input-label for="telefone" :value="'Telefone'" />
                            <x-text-input id="telefone" name="telefone" type="text" value="{{ old('telefone') }}" required data-member-field inputmode="numeric" placeholder="(85) 99999-9999" class="mt-2 block w-full bg-white text-slate-900 dark:bg-[#080d14] dark:text-white" />
                            <x-input-error class="mt-2" :messages="$errors->get('telefone')" />
                        </div>

                        <div>
                            <x-input-label for="data_nascimento" :value="'Nascimento'" />
                            <x-text-input id="data_nascimento" name="data_nascimento" type="date" value="{{ old('data_nascimento') }}" min="{{ now()->subYears(120)->toDateString() }}" max="{{ now()->subYears(5)->toDateString() }}" required data-member-field class="mt-2 block w-full bg-white text-slate-900 dark:bg-[#080d14] dark:text-white" />
                            <x-input-error class="mt-2" :messages="$errors->get('data_nascimento')" />
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                            <i class="ph ph-map-pin"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Contato</p>
                            <h2 class="text-lg font-black text-slate-950 dark:text-white">Endereço do membro</h2>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4">
                        <div>
                            <x-input-label for="endereco" :value="'Endereço completo'" />
                            <x-text-input id="endereco" name="endereco" type="text" value="{{ old('endereco') }}" required data-member-field class="mt-2 block w-full bg-white text-slate-900 dark:bg-[#080d14] dark:text-white" />
                            <x-input-error class="mt-2" :messages="$errors->get('endereco')" />
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                            <i class="ph ph-lock-key"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Acesso</p>
                            <h2 class="text-lg font-black text-slate-950 dark:text-white">Senha inicial</h2>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="password" :value="'Senha'" />
                            <x-text-input id="password" name="password" type="password" required data-member-field class="mt-2 block w-full bg-white text-slate-900 dark:bg-[#080d14] dark:text-white" />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="'Confirmar senha'" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" required data-member-field class="mt-2 block w-full bg-white text-slate-900 dark:bg-[#080d14] dark:text-white" />
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('admin.membros.perfis') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-md border border-slate-200 bg-white px-5 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-5 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800">
                            <i class="ph ph-user-plus"></i>
                            Cadastrar membro
                        </button>
                    </div>
                </section>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('member-create-form');
            if (!form) return;

            const fields = Array.from(form.querySelectorAll('[data-member-field]'));
            const nameInput = document.getElementById('nome');
            const emailInput = document.getElementById('email');
            const cpfInput = document.getElementById('cpf');
            const phoneInput = document.getElementById('telefone');
            const passwordInput = document.getElementById('password');
            const confirmationInput = document.getElementById('password_confirmation');

            const previewInitial = document.getElementById('preview-initial');
            const previewName = document.getElementById('preview-name');
            const previewEmail = document.getElementById('preview-email');
            const previewCpf = document.getElementById('preview-cpf');
            const previewPhone = document.getElementById('preview-phone');
            const previewPassword = document.getElementById('preview-password');
            const progressLabel = document.getElementById('preview-progress-label');
            const progressBar = document.getElementById('preview-progress-bar');

            const formatCpf = (value) => {
                const digits = value.replace(/\D/g, '').slice(0, 11);
                if (digits.length > 9) return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6, 9)}-${digits.slice(9)}`;
                if (digits.length > 6) return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6)}`;
                if (digits.length > 3) return `${digits.slice(0, 3)}.${digits.slice(3)}`;
                return digits;
            };

            const formatPhone = (value) => {
                const digits = value.replace(/\D/g, '').slice(0, 11);
                if (digits.length > 7) return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
                if (digits.length > 2) return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
                if (digits.length > 0) return `(${digits}`;
                return '';
            };

            const updatePreview = () => {
                const name = nameInput?.value.trim() || 'Novo membro';
                const email = emailInput?.value.trim() || 'email ainda não informado';
                const cpf = cpfInput?.value.trim() || '000.000.000-00';
                const phone = phoneInput?.value.trim() || '(00) 00000-0000';
                const password = passwordInput?.value || '';
                const confirmation = confirmationInput?.value || '';
                const filled = fields.filter((field) => field.value.trim() !== '').length;
                const progress = Math.round((filled / fields.length) * 100);

                previewInitial.textContent = name.substring(0, 1).toUpperCase();
                previewName.textContent = name;
                previewEmail.textContent = email;
                previewCpf.textContent = cpf;
                previewPhone.textContent = phone;
                progressLabel.textContent = `${progress}%`;
                progressBar.style.width = `${progress}%`;

                if (!password) {
                    previewPassword.textContent = 'Informe uma senha com pelo menos 8 caracteres.';
                    previewPassword.className = 'mt-3 text-xs font-semibold text-slate-500 dark:text-slate-400';
                } else if (password.length < 8) {
                    previewPassword.textContent = 'Senha curta: use no mínimo 8 caracteres.';
                    previewPassword.className = 'mt-3 text-xs font-semibold text-red-600 dark:text-red-300';
                } else if (confirmation && password !== confirmation) {
                    previewPassword.textContent = 'A confirmação ainda não bate com a senha.';
                    previewPassword.className = 'mt-3 text-xs font-semibold text-amber-700 dark:text-amber-300';
                } else if (confirmation && password === confirmation) {
                    previewPassword.textContent = 'Senha pronta para cadastro.';
                    previewPassword.className = 'mt-3 text-xs font-semibold text-emerald-700 dark:text-emerald-300';
                } else {
                    previewPassword.textContent = 'Senha válida. Falta confirmar.';
                    previewPassword.className = 'mt-3 text-xs font-semibold text-blue-700 dark:text-blue-300';
                }
            };

            cpfInput?.addEventListener('input', () => {
                cpfInput.value = formatCpf(cpfInput.value);
            });

            phoneInput?.addEventListener('input', () => {
                phoneInput.value = formatPhone(phoneInput.value);
            });

            fields.forEach((field) => field.addEventListener('input', updatePreview));
            fields.forEach((field) => field.addEventListener('change', updatePreview));
            updatePreview();
        });
    </script>
</x-app-layout>
