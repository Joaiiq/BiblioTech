<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-emerald-700 dark:text-emerald-300">Pagamento aprovado</p>
                <h1 class="font-serif text-2xl font-black text-slate-950 dark:text-white">Comprovante BiblioPay</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('pagamentos.comprovante.pdf', $pagamento) }}" class="inline-flex h-10 items-center gap-2 rounded-md bg-emerald-600 px-4 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-emerald-700">
                    <i class="ph ph-file-pdf"></i>
                    PDF
                </a>
                <a href="{{ route('membros.situacao') }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                    Situação
                </a>
            </div>
        </div>
    </x-slot>

    <div class="-mx-4 min-h-screen bg-slate-100 px-4 py-8 dark:bg-[#0b1120] sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <main class="mx-auto max-w-3xl">
            <section class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0d1420]">
                <div class="bg-gradient-to-r from-emerald-600 to-blue-700 p-6 text-white">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[.22em] text-emerald-100">Transação simulada</p>
                            <h2 class="mt-2 font-serif text-3xl font-black">R$ {{ number_format($pagamento->valor, 2, ',', '.') }}</h2>
                        </div>
                        <div class="grid h-16 w-16 place-items-center rounded-full bg-white/15">
                            <i class="ph ph-check text-4xl"></i>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 p-6 sm:grid-cols-2">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Código</p>
                        <p class="mt-1 font-mono text-sm font-black text-slate-950 dark:text-white">{{ $pagamento->codigo }}</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Status</p>
                        <p class="mt-1 text-sm font-black text-emerald-700 dark:text-emerald-300">Aprovado</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Método</p>
                        <p class="mt-1 text-sm font-bold text-slate-950 dark:text-white">{{ str_replace('_', ' ', ucfirst($pagamento->metodo)) }}</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Pago em</p>
                        <p class="mt-1 text-sm font-bold text-slate-950 dark:text-white">{{ $pagamento->pago_em?->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="sm:col-span-2 rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Multa regularizada</p>
                        <p class="mt-1 text-sm font-bold text-slate-950 dark:text-white">{{ $pagamento->emprestimo->livro?->titulo ?? 'Livro removido' }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $pagamento->membro->nome }} · {{ $pagamento->referencia }}</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-app-layout>
