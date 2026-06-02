@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumb --}}
    <div class="mb-8 flex items-center gap-2 text-sm">
        <a href="{{ route('admin.membros.perfis') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Perfil de Membros</a>
        <span class="text-slate-500 dark:text-gray-500">›</span>
        <span class="text-slate-700 dark:text-gray-300">{{ $membro->nome }}</span>
    </div>

    {{-- Informações do Membro --}}
    <div class="bg-white dark:bg-[#0d1420] rounded-lg border border-slate-200 dark:border-white/10 p-6 mb-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $membro->nome }}</h1>
                <p class="text-slate-600 dark:text-gray-400 mt-2">ID: {{ $membro->id }} | Carteirinha: <strong>{{ $membro->numero_carteirinha }}</strong></p>
            </div>
            <a href="{{ route('admin.membros.perfis') }}" class="px-4 py-2 bg-slate-200 dark:bg-white/10 text-slate-900 dark:text-white rounded hover:bg-slate-300 dark:hover:bg-white/20 transition">Voltar</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Informações Pessoais</h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <span class="text-slate-600 dark:text-gray-400 w-24">📧 E-mail:</span>
                        <span class="text-slate-900 dark:text-white">{{ $membro->email }}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-slate-600 dark:text-gray-400 w-24">📞 Telefone:</span>
                        <span class="text-slate-900 dark:text-white">{{ $membro->telefone ?? 'Não informado' }}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-slate-600 dark:text-gray-400 w-24">🪪 CPF:</span>
                        <span class="text-slate-900 dark:text-white">{{ $membro->cpf ?? 'Não informado' }}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-slate-600 dark:text-gray-400 w-24">🏠 Endereço:</span>
                        <span class="text-slate-900 dark:text-white">{{ $membro->endereco ?? 'Não informado' }}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-slate-600 dark:text-gray-400 w-24">🎂 Nascimento:</span>
                        <span class="text-slate-900 dark:text-white">
                            @if($membro->data_nascimento)
                                @php
                                    try {
                                        $nasc = $membro->data_nascimento instanceof \Illuminate\Support\Carbon ? $membro->data_nascimento->format('d/m/Y') : \Carbon\Carbon::parse($membro->data_nascimento)->format('d/m/Y');
                                    } catch (\Exception $e) {
                                        $nasc = 'Não informado';
                                    }
                                @endphp
                                {{ $nasc }}
                            @else
                                Não informado
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Estatísticas</h3>
                <div class="space-y-3">
                    <div class="p-3 rounded bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                        <p class="text-xs text-blue-700 dark:text-blue-300">Empréstimos Ativos</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1">{{ $emprestimos->whereIn('status', ['retirado', 'em_uso', 'devolucao_solicitada'])->count() }}</p>
                    </div>
                    <div class="p-3 rounded bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                        <p class="text-xs text-green-700 dark:text-green-300">Empréstimos Completados</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100 mt-1">{{ $emprestimos->where('status', 'encerrado')->count() }}</p>
                    </div>
                    <div class="p-3 rounded bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <p class="text-xs text-red-700 dark:text-red-300">Total de Multa</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100 mt-1">R$ {{ number_format($multasTotal, 2, ',', '.') }}</p>
                    </div>
                    <div class="p-3 rounded @if($atrasados->count() > 0) bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 @else bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 @endif">
                        <p class="text-xs @if($atrasados->count() > 0) text-amber-700 dark:text-amber-300 @else text-slate-700 dark:text-gray-300 @endif">
                            Empréstimos Atrasados
                        </p>
                        <p class="text-2xl font-bold @if($atrasados->count() > 0) text-amber-900 dark:text-amber-100 @else text-slate-900 dark:text-white @endif mt-1">{{ $atrasados->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Empréstimos --}}
    <div class="bg-white dark:bg-[#0d1420] rounded-lg border border-slate-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 dark:bg-white/5 border-b border-slate-200 dark:border-white/10">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Histórico de Empréstimos</h2>
            <p class="text-sm text-slate-600 dark:text-gray-400 mt-1">Total: {{ $emprestimos->count() }} empréstimo(s)</p>
        </div>

        @if($emprestimos->count() > 0)
            <div class="divide-y dark:divide-white/5">
                @foreach($emprestimos as $emp)
                    @php
                        $statusClass = match($emp->status) {
                            'retirado', 'em_uso', 'devolucao_solicitada' => 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500',
                            'encerrado', 'devolvido' => 'bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500',
                            'rejeitado' => 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500',
                            'cancelado' => 'bg-slate-50 dark:bg-white/5 border-l-4 border-slate-400',
                            default => 'bg-slate-50 dark:bg-white/5 border-l-4 border-slate-300 dark:border-white/10',
                        };
                        $isAtrasado = $emp->isAtrasado();
                    @endphp
                    <div class="p-4 {{ $statusClass }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $emp->livro?->titulo ?? 'Livro não encontrado' }}</h3>
                                    <span class="px-2 py-1 text-xs font-bold rounded
                                        @if($emp->status === 'encerrado' || $emp->status === 'devolvido') bg-green-200 text-green-900 dark:bg-green-900/50 dark:text-green-200
                                        @elseif($emp->status === 'retirado' || $emp->status === 'em_uso' || $emp->status === 'devolucao_solicitada') bg-blue-200 text-blue-900 dark:bg-blue-900/50 dark:text-blue-200
                                        @elseif($emp->status === 'rejeitado') bg-red-200 text-red-900 dark:bg-red-900/50 dark:text-red-200
                                        @elseif($emp->status === 'cancelado') bg-slate-200 text-slate-900 dark:bg-slate-700 dark:text-slate-100
                                        @else bg-slate-200 text-slate-900 dark:bg-slate-700 dark:text-slate-100
                                        @endif
                                    ">{{ $emp->status === 'cancelado' ? 'Cancelado' : ucfirst(str_replace('_', ' ', $emp->status)) }}</span>
                                    @if($isAtrasado)
                                        <span class="px-2 py-1 text-xs font-bold rounded bg-red-200 text-red-900 dark:bg-red-900/50 dark:text-red-200">🔴 ATRASADO</span>
                                    @endif
                                </div>
                                <p class="text-sm text-slate-600 dark:text-gray-400">
                                    Autor: <strong>{{ $emp->livro?->autor?->nome ?? 'Não informado' }}</strong> |
                                    Categoria: <strong>{{ $emp->livro?->categoria ?? 'Não informada' }}</strong>
                                </p>

                                <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                    <div>
                                        <p class="text-slate-600 dark:text-gray-400 text-xs">Empréstimo</p>
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $emp->data_emprestimo?->format('d/m/Y') ?? 'Não informado' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-600 dark:text-gray-400 text-xs">Devolução Prevista</p>
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $emp->data_devolucao_prevista?->format('d/m/Y') ?? 'Não informado' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-600 dark:text-gray-400 text-xs">Devolução Real</p>
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $emp->data_devolucao_real?->format('d/m/Y') ?? 'Pendente' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-600 dark:text-gray-400 text-xs">Multa</p>
                                        <p class="font-semibold text-slate-900 dark:text-white">
                                            @if($emp->valor_multa > 0)
                                                <span class="text-red-600 dark:text-red-400">R$ {{ number_format($emp->valor_multa, 2, ',', '.') }}</span>
                                            @else
                                                <span class="text-green-600 dark:text-green-400">Sem multa</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-slate-500 dark:text-gray-500">
                Nenhum empréstimo registrado para este membro.
            </div>
        @endif
    </div>

</div>
@endsection
