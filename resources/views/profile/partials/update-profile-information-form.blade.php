<section>
    <header>
        <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Dados principais</p>
        <h2 class="mt-1 text-xl font-black text-slate-950 dark:text-white font-serif">Informações do perfil</h2>

        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            Atualize as informações do perfil da sua conta e endereço de e-mail.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="profile_photo" :value="'Foto de perfil'" />
            <input
                id="profile_photo"
                name="profile_photo"
                type="file"
                accept="image/*"
                class="mt-2 block w-full rounded-md border border-dashed border-slate-300 bg-slate-50 p-3 text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-[#1E3A8A] file:px-4 file:py-2 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:text-white hover:border-amber-300 dark:border-white/10 dark:bg-[#080d14] dark:text-slate-300"
            >
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">JPG, PNG, GIF ou WEBP até 2MB.</p>
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>

        <div>
            <x-input-label for="name" :value="'Nome'" />
            <x-text-input id="name" name="name" type="text" class="mt-2 block w-full bg-slate-50 text-slate-900 placeholder:text-slate-400 dark:bg-[#0b1120] dark:text-white" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="'E-mail'" />
            <x-text-input id="email" name="email" type="email" class="mt-2 block w-full bg-slate-50 text-slate-900 placeholder:text-slate-400 dark:bg-[#0b1120] dark:text-white" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-md bg-[#1E3A8A] px-5 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-[#0d1420]">
                Salvar perfil
            </button>

            @if (session('sucesso') === 'Perfil atualizado com sucesso.')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-emerald-700 dark:text-emerald-300"
                >Salvo.</p>
            @endif
        </div>
    </form>
</section>
