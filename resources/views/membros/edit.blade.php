<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center gap-1 shrink-0">
                    <i class="ph ph-library text-[#1E3A8A] dark:text-blue-400 text-4xl"></i>
                    <div class="text-[11px] font-black tracking-tight text-center leading-tight">
                        <span class="text-[#1E3A8A] dark:text-blue-400">BIBLIO</span><br>
                        <span class="text-[#F59E0B]">TECH</span>
                    </div>
                </a>
                <div>
                    <h1 class="text-lg font-black text-slate-900 dark:text-white">Editar Membro</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $membro->nome }}</p>
                </div>
            </div>
            <a href="{{ route('admin.membros.show', $membro) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white text-[11px] font-bold uppercase tracking-widest transition">
                <i class="ph ph-arrow-left text-sm"></i>
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="-mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 min-h-screen bg-slate-100 dark:bg-[#0f172a]">
        <div class="max-w-4xl mx-auto">
            <form method="POST" action="{{ route('membros.update', $membro) }}" class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-[#1e293b] rounded-lg p-6 sm:p-8 shadow-sm space-y-6" novalidate>
                @csrf
                @method('PUT')

                @if(session('sucesso'))
                    <div class="rounded-md border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                        {{ session('sucesso') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-md border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                @php
                    $input = 'w-full rounded-md border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]';
                    $label = 'block text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-black mb-1';
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label for="nome" class="{{ $label }}">Nome completo</label>
                        <input id="nome" name="nome" type="text" value="{{ old('nome', $membro->nome) }}" class="{{ $input }}" required>
                    </div>

                    <div>
                        <label for="email" class="{{ $label }}">E-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $membro->email) }}" class="{{ $input }}" required>
                    </div>

                    <div>
                        <label for="cpf" class="{{ $label }}">CPF</label>
                        <input id="cpf" name="cpf" type="text" value="{{ old('cpf', $membro->cpf) }}" class="{{ $input }}" required>
                    </div>

                    <div>
                        <label for="telefone" class="{{ $label }}">Telefone</label>
                        <input id="telefone" name="telefone" type="text" value="{{ old('telefone', $membro->telefone) }}" class="{{ $input }}" required>
                    </div>

                    <div>
                        <label for="data_nascimento" class="{{ $label }}">Nascimento</label>
                        <input id="data_nascimento" name="data_nascimento" type="date" value="{{ old('data_nascimento', $membro->data_nascimento ? \Carbon\Carbon::parse($membro->data_nascimento)->format('Y-m-d') : '') }}" min="{{ now()->subYears(120)->toDateString() }}" max="{{ now()->subYears(5)->toDateString() }}" class="{{ $input }}" required>
                    </div>

                    <div>
                        <label class="{{ $label }}">Perfil</label>
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-black text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                            Membro
                        </div>
                    </div>

                    <div>
                        <label class="{{ $label }}">Carteirinha</label>
                        <div class="rounded-md border border-dashed border-amber-300/60 dark:border-amber-500/40 bg-amber-50 dark:bg-amber-500/10 px-3 py-2 text-sm font-black text-amber-700 dark:text-amber-300">
                            {{ $membro->numero_carteirinha }}
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="endereco" class="{{ $label }}">Endereço</label>
                        <input id="endereco" name="endereco" type="text" value="{{ old('endereco', $membro->endereco) }}" class="{{ $input }}" required>
                    </div>

                    <div>
                        <label for="password" class="{{ $label }}">Nova senha</label>
                        <input id="password" name="password" type="password" class="{{ $input }}" autocomplete="new-password">
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Deixe em branco para manter a senha atual.</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="{{ $label }}">Confirmar nova senha</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="{{ $input }}" autocomplete="new-password">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-md bg-[#1E3A8A] text-white text-xs font-black uppercase tracking-widest hover:bg-blue-800 transition">
                        <i class="ph ph-floppy-disk"></i>
                        Salvar alterações
                    </button>
                    <a href="{{ route('admin.membros.show', $membro) }}" class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-md bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-white/10 transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
