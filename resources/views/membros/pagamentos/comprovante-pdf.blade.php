<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Comprovante {{ $pagamento->codigo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .header { background: #1E3A8A; color: #fff; padding: 22px; border-radius: 8px; }
        .muted { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: .08em; }
        .value { font-size: 15px; font-weight: bold; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .grid td { border: 1px solid #e2e8f0; padding: 12px; width: 50%; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <div style="font-size: 11px; letter-spacing: .16em; text-transform: uppercase;">BiblioPay · Pagamento simulado</div>
        <h1 style="margin: 8px 0 0;">R$ {{ number_format($pagamento->valor, 2, ',', '.') }}</h1>
        <div>Comprovante de regularização de multa</div>
    </div>

    <table class="grid">
        <tr>
            <td><div class="muted">Código</div><div class="value">{{ $pagamento->codigo }}</div></td>
            <td><div class="muted">Status</div><div class="value">Aprovado</div></td>
        </tr>
        <tr>
            <td><div class="muted">Método</div><div class="value">{{ str_replace('_', ' ', ucfirst($pagamento->metodo)) }}</div></td>
            <td><div class="muted">Pago em</div><div class="value">{{ $pagamento->pago_em?->format('d/m/Y H:i') }}</div></td>
        </tr>
        <tr>
            <td><div class="muted">Membro</div><div class="value">{{ $pagamento->membro->nome }}</div>{{ $pagamento->membro->email }}</td>
            <td><div class="muted">Referência</div><div class="value">{{ $pagamento->referencia }}</div></td>
        </tr>
        <tr>
            <td colspan="2"><div class="muted">Livro</div><div class="value">{{ $pagamento->emprestimo->livro?->titulo ?? 'Livro removido' }}</div>{{ $pagamento->emprestimo->livro?->autor?->nome ?? 'Autor não informado' }}</td>
        </tr>
    </table>

    <p style="margin-top: 22px; color: #64748b;">Este comprovante pertence a um ambiente fictício de demonstração acadêmica. Nenhuma transação financeira real foi processada.</p>
</body>
</html>
