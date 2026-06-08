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
                    <h1 class="text-lg font-black text-slate-900 dark:text-white">Painel Perfil do Membro</h1>
                    <p class="text-[11px] text-slate-500 dark:text-gray-500">{{ $membro->nome }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('membros.edit', $membro) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#1E3A8A] text-white text-[11px] font-bold uppercase tracking-widest hover:bg-blue-700 transition">
                    <i class="ph ph-pencil-simple text-sm"></i>
                    Editar
                </a>
                <a href="{{ route('admin.membros.perfis') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white text-[11px] font-bold uppercase tracking-widest transition">
                    <i class="ph ph-arrow-left text-sm"></i>
                    Voltar
                </a>
                <button type="button" @click="dark = !dark" class="w-9 h-9 rounded-md bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/10 transition">
                    <i class="ph ph-sun text-sm hidden dark:inline-block"></i>
                    <i class="ph ph-moon text-sm dark:hidden"></i>
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        .member-detail-bg {
            background:
                radial-gradient(circle at top left, rgba(30,58,138,.10), transparent 32rem),
                radial-gradient(circle at bottom right, rgba(245,158,11,.18), transparent 28rem),
                #eaf0f8;
        }
        .dark .member-detail-bg {
            background:
                radial-gradient(circle at top left, rgba(30,58,138,.20), transparent 32rem),
                radial-gradient(circle at bottom right, rgba(245,158,11,.10), transparent 28rem),
                #0f172a;
        }
        .member-panel {
            background: rgba(248,250,252,.92);
            border-color: rgba(148,163,184,.45);
            box-shadow: 0 18px 45px rgba(15,23,42,.08);
        }
        .dark .member-panel {
            background: #111827;
            border-color: #1e293b;
            box-shadow: none;
        }
        .bg-shelf { background: linear-gradient(90deg, transparent, rgba(30,58,138,.18) 20%, rgba(245,158,11,.25) 80%, transparent); }
        .dark .bg-shelf { background: linear-gradient(90deg, transparent, rgba(147,197,253,.07) 20%, rgba(245,158,11,.10) 80%, transparent); }
        .bg-icon { color: rgba(30,58,138,.13); pointer-events: none; user-select: none; }
        .book-icon { color: rgba(245,158,11,.40); }
        .dark .bg-icon { color: rgba(147,197,253,.07); }
        .dark .book-icon { color: rgba(245,158,11,.22); }
        #bg-glow-det-1 { background: radial-gradient(circle, rgba(30,58,138,.20) 0%, transparent 70%); }
        #bg-glow-det-2 { background: radial-gradient(circle, rgba(245,158,11,.28) 0%, transparent 70%); }
        .dark #bg-glow-det-1 { background: radial-gradient(circle, rgba(30,58,138,.25) 0%, transparent 70%); }
        .dark #bg-glow-det-2 { background: radial-gradient(circle, rgba(245,158,11,.12) 0%, transparent 70%); }
        .situacao-badge-bom { background: #dcfce7; color: #047857; border-color: #86efac; }
        .situacao-badge-devendo { background: #fef3c7; color: #b45309; border-color: #fcd34d; }
        .situacao-badge-multa { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }
        .dark .situacao-badge-bom { background: rgba(6,78,59,.35); color: #a7f3d0; border-color: rgba(16,185,129,.45); }
        .dark .situacao-badge-devendo { background: rgba(120,53,15,.35); color: #fcd34d; border-color: rgba(245,158,11,.45); }
        .dark .situacao-badge-multa { background: rgba(127,29,29,.35); color: #fecaca; border-color: rgba(239,68,68,.45); }
    </style>

    @php
        $ativos = $emprestimos->whereIn('status', \App\Models\Emprestimos::STATUS_EM_ANDAMENTO)->count();
        $encerrados = $emprestimos->where('status', \App\Models\Emprestimos::STATUS_ENCERRADO)->count();
        $totalAtrasados = $atrasados->count();
        $multaPendente = $emprestimos
            ->where('status', \App\Models\Emprestimos::STATUS_DEVOLVIDO)
            ->where('valor_multa', '>', 0)
            ->sum('valor_multa');
        $statusGeral = $multaPendente > 0 ? 'multa' : ($totalAtrasados > 0 ? 'devendo' : 'bom');
        $statusLabel = $statusGeral === 'multa' ? 'Com multa' : ($statusGeral === 'devendo' ? 'Devendo' : 'Em dia');
        $statusClasses = $statusGeral === 'multa'
            ? 'situacao-badge-multa'
            : ($statusGeral === 'devendo'
                ? 'situacao-badge-devendo'
                : 'situacao-badge-bom');
    @endphp

    <div class="-mx-4 px-4 py-8 member-detail-bg sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 min-h-screen relative">
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="bg-dots-det" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#93c5fd" opacity="0.07"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#bg-dots-det)"/>
            </svg>
            <div id="bg-glow-det-1" class="absolute -top-28 -left-20 w-96 h-96 rounded-full blur-[90px]"></div>
            <div id="bg-glow-det-2" class="absolute -bottom-20 right-10 w-80 h-80 rounded-full blur-[80px]"></div>
            <div class="bg-shelf absolute left-0 right-0 h-px top-[30%]"></div>
            <div class="bg-shelf absolute left-0 right-0 h-px top-[65%]"></div>
            <i class="ph ph-identification-card bg-icon absolute left-[72%] top-[50%] text-[26px]"></i>
            <i class="ph ph-book-open bg-icon book-icon absolute left-[12%] top-[55%] text-[34px]"></i>
        </div>

        <div class="max-w-7xl mx-auto relative z-10 space-y-5">
            <div class="member-panel border rounded-md p-5">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div class="flex items-start gap-4 min-w-0">
                        <x-user-avatar :user="$membro" size="h-14 w-14" text="text-base" />
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl font-black text-slate-900 dark:text-white">{{ $membro->nome }}</h2>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $statusClasses }}">
                                    @if($statusGeral === 'multa')
                                        <i class="ph ph-coins"></i>
                                    @elseif($statusGeral === 'devendo')
                                        <i class="ph ph-warning-circle"></i>
                                    @else
                                        <i class="ph ph-check-circle"></i>
                                    @endif
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500 dark:text-gray-500">{{ $membro->email }}</p>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-2 text-xs">
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-slate-500">Carteirinha</p>
                                    <p class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $membro->numero_carteirinha ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-slate-500">CPF</p>
                                    <p class="text-slate-900 dark:text-white">{{ $membro->cpf ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-slate-500">Telefone</p>
                                    <p class="text-slate-900 dark:text-white">{{ $membro->telefone ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-slate-500">Perfil</p>
                                    <p class="text-slate-900 dark:text-white">Membro</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <button type="button" id="openPasswordModalBtn" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md bg-amber-500 border border-amber-600 text-slate-950 text-[11px] font-black uppercase tracking-widest hover:bg-amber-400 transition">
                            <i class="ph ph-key text-sm"></i>
                            Redefinir senha
                        </button>
                        <button type="button" id="openMsgModalBtn" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md bg-[#1E3A8A] border border-blue-800 text-white text-[11px] font-black uppercase tracking-widest hover:bg-blue-700 transition">
                            <i class="ph ph-envelope-simple text-sm"></i>
                            Enviar Mensagem
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="member-panel border rounded-md p-4">
                    <p class="text-[10px] uppercase tracking-widest text-slate-500">Ativos</p>
                    <p class="mt-1 text-2xl font-black text-blue-500">{{ $ativos }}</p>
                </div>
                <div class="member-panel border rounded-md p-4">
                    <p class="text-[10px] uppercase tracking-widest text-slate-500">Atrasados</p>
                    <p class="mt-1 text-2xl font-black {{ $totalAtrasados > 0 ? 'text-amber-400' : 'text-slate-900 dark:text-white' }}">{{ $totalAtrasados }}</p>
                </div>
                <div class="member-panel border rounded-md p-4">
                    <p class="text-[10px] uppercase tracking-widest text-slate-500">Concluídos</p>
                    <p class="mt-1 text-2xl font-black text-emerald-500">{{ $encerrados }}</p>
                </div>
                <div class="member-panel border rounded-md p-4">
                    <p class="text-[10px] uppercase tracking-widest text-slate-500">Multa pendente</p>
                    <p class="mt-1 text-2xl font-black {{ $multaPendente > 0 ? 'text-red-400' : 'text-slate-900 dark:text-white' }}">R$ {{ number_format($multaPendente, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="member-panel border rounded-md p-5">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest mb-4">Dados cadastrais</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-slate-500">Endereço</p>
                            <p class="mt-1 text-sm text-slate-900 dark:text-white">{{ $membro->endereco ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-slate-500">Nascimento</p>
                            <p class="mt-1 text-sm text-slate-900 dark:text-white">
                                @if($membro->data_nascimento)
                                    {{ \Carbon\Carbon::parse($membro->data_nascimento)->format('d/m/Y') }}
                                @else
                                    Não informado
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-slate-500">Criado em</p>
                            <p class="mt-1 text-sm text-slate-900 dark:text-white">{{ $membro->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 member-panel border rounded-md overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 dark:border-[#1e293b] flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <i class="ph ph-book-bookmark text-[#F59E0B] text-base"></i>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest">Histórico de empréstimos</h3>
                        </div>
                        <span class="text-[10px] text-slate-400 dark:text-gray-500 bg-slate-100 dark:bg-white/5 px-2 py-0.5 rounded-md font-bold">{{ $emprestimos->count() }} registros</span>
                    </div>

                    @if($emprestimos->count() > 0)
                        <div class="overflow-x-auto overscroll-x-contain pb-2">
                            <table class="min-w-[860px] w-full text-left">
                                <thead>
                                    <tr class="text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200 dark:border-[#1e293b]">
                                        <th class="px-5 py-3 text-left">Livro</th>
                                        <th class="px-5 py-3 text-left">Datas</th>
                                        <th class="px-5 py-3 text-left">Status</th>
                                        <th class="px-5 py-3 text-left">Multa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emprestimos as $emp)
                                        @php
                                            $isAtrasado = $emp->isAtrasado();
                                            $multaPrevista = $isAtrasado ? \App\Models\Emprestimos::calcularMulta($emp->data_devolucao_prevista) : 0;
                                            $statusLabel = match ($emp->status) {
                                                \App\Models\Emprestimos::STATUS_SOLICITADO => 'Solicitado',
                                                \App\Models\Emprestimos::STATUS_APROVADO => 'Aprovado',
                                                \App\Models\Emprestimos::STATUS_RETIRADO => 'Retirado',
                                                \App\Models\Emprestimos::STATUS_EM_USO => 'Em uso',
                                                \App\Models\Emprestimos::STATUS_DEVOLUCAO_SOLICITADA => 'Devolução solicitada',
                                                \App\Models\Emprestimos::STATUS_DEVOLVIDO => 'Devolvido',
                                                \App\Models\Emprestimos::STATUS_ENCERRADO => 'Encerrado',
                                                \App\Models\Emprestimos::STATUS_REJEITADO => 'Rejeitado',
                                                \App\Models\Emprestimos::STATUS_CANCELADO => 'Cancelado',
                                                default => ucfirst(str_replace('_', ' ', $emp->status)),
                                            };
                                        @endphp
                                        <tr class="border-b border-slate-100 dark:border-[#1e293b] hover:bg-slate-50 dark:hover:bg-white/[.03]">
                                            <td class="px-5 py-4 text-left">
                                                <div class="flex items-center gap-3 min-w-[220px]">
                                                    <div class="w-10 h-12 rounded-md bg-amber-100/80 dark:bg-amber-900/20 border border-amber-300/60 dark:border-amber-800/40 flex items-center justify-center overflow-hidden shrink-0">
                                                        @if($emp->livro?->capa)
                                                            <img src="{{ asset('storage/' . $emp->livro->capa) }}" alt="{{ $emp->livro->titulo }}" class="w-full h-full object-cover">
                                                        @else
                                                            <i class="ph ph-book text-[#F59E0B]"></i>
                                                        @endif
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $emp->livro?->titulo ?? 'Livro removido' }}</p>
                                                        <p class="text-[11px] text-slate-500 dark:text-gray-500 truncate">{{ $emp->livro?->autor?->nome ?? 'Autor desconhecido' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 text-left">
                                                <div class="space-y-1 min-w-[150px] text-xs">
                                                    <p class="text-slate-500 dark:text-gray-500">Empréstimo: <span class="text-slate-900 dark:text-white font-semibold">{{ $emp->data_emprestimo?->format('d/m/Y') ?? '—' }}</span></p>
                                                    <p class="{{ $isAtrasado ? 'text-amber-400 font-bold' : 'text-slate-500 dark:text-gray-500' }}">Prazo: <span>{{ $emp->data_devolucao_prevista?->format('d/m/Y') ?? '—' }}</span></p>
                                                    <p class="text-slate-500 dark:text-gray-500">Devolução: <span class="text-slate-900 dark:text-white font-semibold">{{ $emp->data_devolucao_real?->format('d/m/Y') ?? 'Pendente' }}</span></p>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 text-left">
                                                <div class="space-y-1">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $isAtrasado ? 'bg-amber-900/30 text-amber-300 border-amber-800/50' : 'bg-slate-900/40 text-slate-300 border-slate-700' }}">
                                                        @if($isAtrasado)
                                                            <i class="ph ph-warning-circle"></i>
                                                            Atrasado
                                                        @else
                                                            {{ $statusLabel }}
                                                        @endif
                                                    </span>
                                                    @if($emp->rejected_reason)
                                                        <p class="text-[11px] text-red-300">{{ $emp->rejected_reason }}</p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 text-left">
                                                @if($emp->valor_multa > 0)
                                                    <p class="text-sm font-black text-red-400">R$ {{ number_format($emp->valor_multa, 2, ',', '.') }}</p>
                                                @elseif($multaPrevista > 0)
                                                    <p class="text-sm font-black text-amber-400">R$ {{ number_format($multaPrevista, 2, ',', '.') }}</p>
                                                    <p class="text-[10px] text-slate-500">prevista</p>
                                                @else
                                                    <p class="text-sm font-bold text-emerald-400">Sem multa</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-16 flex flex-col items-center justify-center text-center">
                            <i class="ph ph-books text-slate-300 dark:text-slate-700 text-5xl mb-3"></i>
                            <p class="text-slate-400 dark:text-slate-600 font-bold">Nenhum empréstimo registrado</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="messageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-lg mx-4">
            <div class="bg-white dark:bg-[#0d1420] rounded-md p-6 border border-slate-200 dark:border-white/10 shadow-2xl">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <i class="ph ph-envelope-simple text-blue-500 text-lg"></i>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Enviar Mensagem</h4>
                    </div>
                    <button type="button" id="closeModal" class="w-8 h-8 rounded-md bg-slate-100 dark:bg-white/5 text-slate-500 hover:text-slate-900 dark:hover:text-white transition flex items-center justify-center">
                        <i class="ph ph-x text-sm"></i>
                    </button>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-gray-500 mb-4 uppercase tracking-wider">Para: <strong class="text-slate-900 dark:text-white normal-case tracking-normal text-sm">{{ $membro->nome }}</strong></p>
                <form id="messageForm" class="space-y-4">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-1 block">Assunto</label>
                        <input type="text" name="subject" id="msgSubject" class="w-full px-3 py-2 rounded-md border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 transition" required>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-1 block">Mensagem</label>
                        <textarea name="message" id="msgBody" rows="5" class="w-full px-3 py-2 rounded-md border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/30 transition resize-none" required></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" id="cancelMsgBtn" class="px-4 py-2 rounded-md bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-gray-300 text-[11px] font-bold uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-white/10 transition">Cancelar</button>
                        <button type="submit" class="px-5 py-2 rounded-md bg-[#1E3A8A] border border-blue-800 text-white text-[11px] font-black uppercase tracking-widest hover:bg-blue-700 transition">
                            <i class="ph ph-paper-plane-right mr-1"></i>
                            Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="passwordModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-lg mx-4">
            <div class="bg-white dark:bg-[#0d1420] rounded-md p-6 border border-slate-200 dark:border-white/10 shadow-2xl">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <i class="ph ph-key text-amber-500 text-lg"></i>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Redefinir senha do membro</h4>
                    </div>
                    <button type="button" id="closePasswordModal" class="w-8 h-8 rounded-md bg-slate-100 dark:bg-white/5 text-slate-500 hover:text-slate-900 dark:hover:text-white transition flex items-center justify-center">
                        <i class="ph ph-x text-sm"></i>
                    </button>
                </div>

                <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">
                    Defina uma senha temporária para <strong class="text-slate-900 dark:text-white">{{ $membro->nome }}</strong>. Depois informe a senha ao membro pelo atendimento.
                </p>

                <form method="POST" action="{{ route('admin.membros.password', $membro) }}" class="mt-5 space-y-4" data-confirm="loan" data-title="Redefinir senha?" data-text="A senha atual do membro será substituída pela nova senha informada.">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="member_password" class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-1 block">Nova senha</label>
                        <input id="member_password" name="password" type="password" autocomplete="new-password" minlength="8" required class="w-full px-3 py-2 rounded-md border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="member_password_confirmation" class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-gray-500 mb-1 block">Confirmar nova senha</label>
                        <input id="member_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required class="w-full px-3 py-2 rounded-md border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" id="cancelPasswordBtn" class="px-4 py-2 rounded-md bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-gray-300 text-[11px] font-bold uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-white/10 transition">Cancelar</button>
                        <button type="submit" class="px-5 py-2 rounded-md bg-amber-500 border border-amber-600 text-slate-950 text-[11px] font-black uppercase tracking-widest hover:bg-amber-400 transition">
                            <i class="ph ph-floppy-disk mr-1"></i>
                            Salvar senha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('messageModal');
        const form = document.getElementById('messageForm');
        const passwordModal = document.getElementById('passwordModal');

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        function openPasswordModal() {
            passwordModal.classList.remove('hidden');
            passwordModal.classList.add('flex');
        }

        function closePasswordModal() {
            passwordModal.classList.remove('flex');
            passwordModal.classList.add('hidden');
        }

        document.getElementById('openMsgModalBtn')?.addEventListener('click', openModal);
        document.getElementById('closeModal')?.addEventListener('click', closeModal);
        document.getElementById('cancelMsgBtn')?.addEventListener('click', closeModal);
        modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
        document.getElementById('openPasswordModalBtn')?.addEventListener('click', openPasswordModal);
        document.getElementById('closePasswordModal')?.addEventListener('click', closePasswordModal);
        document.getElementById('cancelPasswordBtn')?.addEventListener('click', closePasswordModal);
        passwordModal.addEventListener('click', event => { if (event.target === passwordModal) closePasswordModal(); });

        form?.addEventListener('submit', async event => {
            event.preventDefault();
            const formData = new FormData(form);
            const response = await fetch(`/admin/membros/{{ $membro->id }}/message`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    subject: formData.get('subject'),
                    message: formData.get('message'),
                }),
            });

            if (response.ok) {
                closeModal();
                if (typeof Swal !== 'undefined') {
                    Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, background: '#0d1420', color: '#fff' })
                        .fire({ icon: 'success', title: 'Mensagem enviada' });
                }
            }
        });
    });
    </script>
</x-app-layout>
