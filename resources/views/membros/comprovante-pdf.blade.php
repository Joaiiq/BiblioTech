<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1f2937; background: #fff; padding: 40px; }

        .header { text-align: center; border-bottom: 3px solid #1E3A8A; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { font-size: 28px; color: #1E3A8A; letter-spacing: 2px; }
        .header p { font-size: 11px; color: #6b7280; margin-top: 4px; }

        .badge { display: inline-block; padding: 4px 12px; font-size: 10px; font-weight: bold;
                 text-transform: uppercase; letter-spacing: 1px; border-radius: 2px; }
        .badge-ativo    { background: #dbeafe; color: #1e40af; }
        .badge-atrasado { background: #fee2e2; color: #dc2626; }
        .badge-concluido{ background: #dcfce7; color: #16a34a; }

        .section { margin-bottom: 24px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase;
                         letter-spacing: 2px; color: #6b7280; margin-bottom: 10px;
                         border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }

        .grid { display: table; width: 100%; }
        .row { display: table-row; }
        .cell { display: table-cell; padding: 6px 0; font-size: 12px; width: 50%; }
        .label { color: #6b7280; font-size: 11px; }
        .value { color: #111827; font-weight: bold; font-size: 12px; }

        .alert { padding: 12px 16px; border-radius: 4px; font-size: 12px; margin-top: 16px; }
        .alert-red { background: #fee2e2; color: #dc2626; border-left: 4px solid #dc2626; }
        .alert-green { background: #dcfce7; color: #16a34a; border-left: 4px solid #16a34a; }
        .alert-blue { background: #dbeafe; color: #1e40af; border-left: 4px solid #1e40af; }

        .footer { margin-top: 60px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 16px; }
    </style>
</head>
<body>

<div class="header">
    <h1>BiblioTech</h1>
    <p>Comprovante de Empréstimo #{{ str_pad($emprestimo->id, 6, '0', STR_PAD_LEFT) }}</p>
    <p style="margin-top:8px;">
        @php
            $atrasado = !$emprestimo->data_devolucao_real &&
                \Carbon\Carbon::today()->greaterThan($emprestimo->data_devolucao_prevista);
            $ativo = !$emprestimo->data_devolucao_real;
            $devolucaoSolicitada = $emprestimo->status === \App\Models\Emprestimos::STATUS_DEVOLUCAO_SOLICITADA;
        @endphp

        @if($atrasado)
            <span class="badge badge-atrasado">Atrasado</span>
        @elseif($devolucaoSolicitada)
            <span class="badge badge-ativo">Devolução solicitada</span>
        @elseif($ativo)
            <span class="badge badge-ativo">Em andamento</span>
        @else
            <span class="badge badge-concluido">Concluído</span>
        @endif
    </p>
</div>

<div class="section">
    <div class="section-title">Dados do Membro</div>
    <div class="grid">
        <div class="row">
            <div class="cell"><span class="label">Nome</span><br><span class="value">{{ $emprestimo->membro->nome }}</span></div>
            <div class="cell"><span class="label">E-mail</span><br><span class="value">{{ $emprestimo->membro->email }}</span></div>
        </div>
        <div class="row">
            <div class="cell"><span class="label">Carteirinha</span><br><span class="value">{{ $emprestimo->membro->numero_carteirinha ?? '—' }}</span></div>
            <div class="cell"><span class="label">Perfil</span><br><span class="value">Membro</span></div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Dados da Obra</div>
    <div class="grid">
        <div class="row">
            <div class="cell"><span class="label">Título</span><br><span class="value">{{ $emprestimo->livro->titulo }}</span></div>
            <div class="cell"><span class="label">Autor</span><br><span class="value">{{ $emprestimo->livro->autor->nome ?? '—' }}</span></div>
        </div>
        <div class="row">
            <div class="cell"><span class="label">ISBN</span><br><span class="value">{{ $emprestimo->livro->isbn }}</span></div>
            <div class="cell"><span class="label">Categoria</span><br><span class="value">{{ $emprestimo->livro->categoria }}</span></div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Datas do Empréstimo</div>
    <div class="grid">
        <div class="row">
            <div class="cell"><span class="label">Data de Retirada</span><br><span class="value">{{ $emprestimo->data_emprestimo?->format('d/m/Y') ?? 'Aguardando retirada' }}</span></div>
            <div class="cell"><span class="label">Prazo de Devolução</span><br><span class="value">{{ $emprestimo->data_devolucao_prevista?->format('d/m/Y') ?? 'A definir' }}</span></div>
        </div>
        @if($emprestimo->data_devolucao_real)
        <div class="row">
            <div class="cell"><span class="label">Data de Devolução</span><br><span class="value">{{ $emprestimo->data_devolucao_real->format('d/m/Y') }}</span></div>
            <div class="cell"></div>
        </div>
        @elseif($emprestimo->return_requested_at)
        <div class="row">
            <div class="cell"><span class="label">Solicitação de Devolução</span><br><span class="value">{{ $emprestimo->return_requested_at->format('d/m/Y \à\s H:i') }}</span></div>
            <div class="cell"><span class="label">Conferência da Biblioteca</span><br><span class="value">Pendente</span></div>
        </div>
        @endif
    </div>

    @if($emprestimo->valor_multa > 0)
        <div class="alert alert-red" style="margin-top:12px;">
            <strong>Multa gerada:</strong> R$ {{ number_format($emprestimo->valor_multa, 2, ',', '.') }}
        </div>
    @elseif($atrasado)
        <div class="alert alert-red">
            <strong>Atenção:</strong> Este empréstimo está em atraso. Uma multa será calculada na devolução.
        </div>
    @elseif($devolucaoSolicitada)
        <div class="alert alert-blue">
            <strong>Devolução solicitada.</strong> O pedido foi registrado em {{ $emprestimo->return_requested_at?->format('d/m/Y \à\s H:i') ?? 'data não registrada' }} e aguarda conferência da biblioteca.
        </div>
    @elseif($ativo)
        <div class="alert alert-blue">
            <strong>Empréstimo ativo.</strong> O prazo previsto é {{ $emprestimo->data_devolucao_prevista?->format('d/m/Y') ?? 'a data definida pela biblioteca' }}. A data real só aparece depois da conferência da biblioteca.
        </div>
    @else
        <div class="alert alert-green">
            <strong>Devolvido no prazo.</strong> Sem multas. Obrigado!
        </div>
    @endif
</div>

<div class="footer">
    Documento gerado em {{ now()->format('d/m/Y \à\s H:i') }} &bull; BiblioTech &bull; Este documento não tem validade jurídica.
</div>

</body>
</html>
