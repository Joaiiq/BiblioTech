<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('membros.situacao') }}" class="flex flex-col items-center justify-center gap-1 shrink-0">
                    <i class="ph ph-credit-card text-4xl text-[#1E3A8A] dark:text-blue-400"></i>
                    <div class="text-center text-[11px] font-black leading-tight tracking-tight">
                        <span class="text-[#1E3A8A] dark:text-blue-400">BIBLIO</span><br>
                        <span class="text-[#F59E0B]">PAY</span>
                    </div>
                </a>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-amber-700 dark:text-amber-300">Pagamento simulado</p>
                    <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Regularizar multa</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ambiente fictício para demonstração acadêmica</p>
                </div>
            </div>

            <a href="{{ route('membros.situacao') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                <i class="ph ph-arrow-left"></i>
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="-mx-4 min-h-screen bg-gradient-to-b from-slate-100 via-blue-50 to-slate-100 px-4 py-8 dark:from-[#0f172a] dark:via-[#111827] dark:to-[#0b1120] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <main class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <section x-data="{ metodo: 'pix' }" class="overflow-hidden rounded-md border border-slate-200 bg-white/95 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                <div class="border-b border-slate-200 p-5 dark:border-white/10 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.18em] text-blue-700 dark:text-blue-300">Checkout</p>
                            <h2 class="mt-1 font-serif text-3xl font-black text-slate-950 dark:text-white">Escolha como pagar</h2>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            </span>
                            Simulador ativo
                        </span>
                    </div>

                    @if(session('erro'))
                        <div class="mt-4 rounded-md border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-700 dark:text-red-300">
                            {{ session('erro') }}
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('pagamentos.pagar', $emprestimo) }}" class="space-y-6 p-5 sm:p-6" data-confirm="loan" data-title="Confirmar pagamento?" data-text="O sistema vai aprovar o pagamento fictício e liberar sua conta.">
                    @csrf

                    <div class="grid gap-3 md:grid-cols-3">
                        <label class="group cursor-pointer">
                            <input type="radio" name="metodo" value="pix" x-model="metodo" class="peer sr-only">
                            <span class="flex h-full flex-col rounded-md border border-slate-200 bg-slate-50 p-4 transition peer-checked:border-emerald-400 peer-checked:bg-emerald-50 peer-checked:ring-2 peer-checked:ring-emerald-400/20 dark:border-white/10 dark:bg-white/[.03] dark:peer-checked:border-emerald-400/50 dark:peer-checked:bg-emerald-500/10">
                                <i class="ph ph-qr-code text-3xl text-emerald-600 dark:text-emerald-300"></i>
                                <strong class="mt-4 text-sm text-slate-950 dark:text-white">Pix demo</strong>
                                <span class="mt-1 text-xs text-slate-500 dark:text-slate-400">Aprovação instantânea.</span>
                            </span>
                        </label>

                        <label class="group cursor-pointer">
                            <input type="radio" name="metodo" value="cartao" x-model="metodo" class="peer sr-only">
                            <span class="flex h-full flex-col rounded-md border border-slate-200 bg-slate-50 p-4 transition peer-checked:border-blue-400 peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-blue-400/20 dark:border-white/10 dark:bg-white/[.03] dark:peer-checked:border-blue-400/50 dark:peer-checked:bg-blue-500/10">
                                <i class="ph ph-credit-card text-3xl text-blue-700 dark:text-blue-300"></i>
                                <strong class="mt-4 text-sm text-slate-950 dark:text-white">Cartão fictício</strong>
                                <span class="mt-1 text-xs text-slate-500 dark:text-slate-400">Use qualquer final exceto 0000.</span>
                            </span>
                        </label>

                        <label class="group cursor-pointer">
                            <input type="radio" name="metodo" value="saldo_biblioteca" x-model="metodo" class="peer sr-only">
                            <span class="flex h-full flex-col rounded-md border border-slate-200 bg-slate-50 p-4 transition peer-checked:border-amber-400 peer-checked:bg-amber-50 peer-checked:ring-2 peer-checked:ring-amber-400/20 dark:border-white/10 dark:bg-white/[.03] dark:peer-checked:border-amber-400/50 dark:peer-checked:bg-amber-500/10">
                                <i class="ph ph-wallet text-3xl text-amber-700 dark:text-amber-300"></i>
                                <strong class="mt-4 text-sm text-slate-950 dark:text-white">Saldo biblioteca</strong>
                                <span class="mt-1 text-xs text-slate-500 dark:text-slate-400">Crédito interno simulado.</span>
                            </span>
                        </label>
                    </div>

                    <div x-show="metodo === 'pix'" x-transition class="rounded-md border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                        <div class="grid gap-5 md:grid-cols-[150px_minmax(0,1fr)] md:items-center">
                            <div class="grid aspect-square place-items-center rounded-md border border-emerald-200 bg-white p-3 dark:border-emerald-500/30 dark:bg-[#0d1420]">
                                <div class="grid h-full w-full grid-cols-5 gap-1">
                                    @for($i = 0; $i < 25; $i++)
                                        <span class="rounded-sm {{ in_array($i, [0,1,2,5,10,11,12,14,16,18,20,22,23,24], true) ? 'bg-slate-950 dark:bg-white' : 'bg-emerald-200 dark:bg-emerald-500/40' }}"></span>
                                    @endfor
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Pix copia e cola fictício</p>
                                <p class="mt-2 break-all rounded-md bg-white p-3 font-mono text-xs text-slate-700 dark:bg-[#0d1420] dark:text-slate-300">00020126BIBLIOTECH.PAY.SIMULADO.{{ str_pad((string) $emprestimo->id, 6, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                    </div>

                    <div x-show="metodo === 'cartao'" x-transition class="grid gap-4 rounded-md border border-blue-200 bg-blue-50 p-5 dark:border-blue-500/30 dark:bg-blue-500/10 md:grid-cols-2">
                        <div>
                            <label for="card_name" class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">Nome no cartão</label>
                            <input id="card_name" name="card_name" type="text" class="h-11 w-full rounded-md border border-blue-200 bg-white px-3 text-sm text-slate-900 outline-none focus:border-blue-600 dark:border-blue-500/30 dark:bg-[#0d1420] dark:text-white">
                        </div>
                        <div>
                            <label for="card_last_digits" class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-300">Últimos 4 dígitos</label>
                            <input id="card_last_digits" name="card_last_digits" type="text" inputmode="numeric" maxlength="4" placeholder="1234" class="h-11 w-full rounded-md border border-blue-200 bg-white px-3 text-sm text-slate-900 outline-none focus:border-blue-600 dark:border-blue-500/30 dark:bg-[#0d1420] dark:text-white">
                        </div>
                        <p class="md:col-span-2 text-xs text-blue-800 dark:text-blue-200">Dica de teste: final <strong>0000</strong> simula recusa; qualquer outro final aprova.</p>
                    </div>

                    <div x-show="metodo === 'saldo_biblioteca'" x-transition class="rounded-md border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/30 dark:bg-amber-500/10">
                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">O saldo biblioteca representa crédito registrado presencialmente. Neste simulador, a baixa é aprovada no ato.</p>
                    </div>

                    <x-input-error class="mt-2" :messages="$errors->get('metodo')" />
                    <x-input-error class="mt-2" :messages="$errors->get('card_name')" />
                    <x-input-error class="mt-2" :messages="$errors->get('card_last_digits')" />

                    <button type="submit" class="group inline-flex h-12 w-full items-center justify-center gap-2 rounded-md bg-[#1E3A8A] px-5 text-[11px] font-black uppercase tracking-widest text-white shadow-lg shadow-blue-900/20 transition hover:-translate-y-0.5 hover:bg-blue-800">
                        <i class="ph ph-lock-key text-lg"></i>
                        Confirmar pagamento de R$ {{ number_format($emprestimo->valor_multa, 2, ',', '.') }}
                        <i class="ph ph-arrow-right text-lg transition group-hover:translate-x-1"></i>
                    </button>
                </form>
            </section>

            <aside class="space-y-4">
                <section class="rounded-md border border-slate-200 bg-white/95 p-5 shadow-sm dark:border-white/10 dark:bg-[#0d1420]/95">
                    <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-500 dark:text-slate-400">Resumo</p>
                    <h2 class="mt-2 font-serif text-2xl font-black text-slate-950 dark:text-white">R$ {{ number_format($emprestimo->valor_multa, 2, ',', '.') }}</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Livro</p>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $emprestimo->livro?->titulo ?? 'Livro removido' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Prazo</p>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $emprestimo->data_devolucao_prevista?->format('d/m/Y') ?? '--' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Devolução</p>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $emprestimo->data_devolucao_real?->format('d/m/Y') ?? '--' }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/30 dark:bg-amber-500/10">
                    <div class="flex items-start gap-3">
                        <i class="ph ph-info text-2xl text-amber-700 dark:text-amber-300"></i>
                        <p class="text-sm leading-relaxed text-amber-900 dark:text-amber-100">Este pagamento é fictício. Ele serve para demonstrar fluxo financeiro, baixa de multa, comprovante e desbloqueio do membro.</p>
                    </div>
                </section>
            </aside>
        </main>
    </div>
</x-app-layout>
