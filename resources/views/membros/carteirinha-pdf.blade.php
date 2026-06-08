<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; background: #ffffff; padding: 40px; }
        .card { border: 2px solid #1E3A8A; border-radius: 8px; overflow: hidden; }
        .top { background: #1E3A8A; color: #ffffff; padding: 26px; }
        .top-table { width: 100%; }
        .top-info { vertical-align: top; }
        .photo-cell { width: 96px; text-align: right; vertical-align: top; }
        .photo { width: 82px; height: 82px; border: 2px solid rgba(255,255,255,.45); border-radius: 6px; object-fit: cover; }
        .photo-fallback { width: 82px; height: 82px; border: 2px solid rgba(255,255,255,.45); border-radius: 6px; text-align: center; line-height: 78px; font-size: 28px; font-weight: bold; background: rgba(255,255,255,.12); color: #ffffff; }
        .brand { color: #F59E0B; font-size: 12px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        h1 { font-size: 28px; margin-top: 8px; }
        .email { font-size: 12px; color: #dbeafe; margin-top: 4px; }
        .body { padding: 24px; }
        .number { background: #fffbeb; border: 1px solid #f59e0b; border-radius: 6px; padding: 16px; margin-bottom: 20px; }
        .label { color: #6b7280; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; }
        .value { color: #111827; font-size: 13px; font-weight: bold; margin-top: 4px; }
        .card-number { color: #92400e; font-family: DejaVu Sans Mono, monospace; font-size: 24px; font-weight: bold; margin-top: 6px; }
        .grid { display: table; width: 100%; }
        .row { display: table-row; }
        .cell { display: table-cell; width: 50%; padding: 10px 8px 10px 0; vertical-align: top; }
        .status-ok { color: #047857; }
        .status-bad { color: #b91c1c; }
        .footer { margin-top: 28px; border-top: 1px solid #e5e7eb; padding-top: 14px; color: #6b7280; font-size: 10px; text-align: center; }
    </style>
</head>
<body>
@php
    $regular = $atrasados === 0 && (float) $multasPendentes <= 0;
    $nascimento = $membro->data_nascimento ? \Carbon\Carbon::parse($membro->data_nascimento)->format('d/m/Y') : 'Não informado';
    $photoData = null;
    if ($membro->profile_photo_path) {
        $photoPath = public_path('storage/' . $membro->profile_photo_path);
        if (is_file($photoPath)) {
            $photoData = 'data:' . mime_content_type($photoPath) . ';base64,' . base64_encode(file_get_contents($photoPath));
        }
    }
    $initials = collect(explode(' ', $membro->nome ?? 'BT'))
        ->filter()
        ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->join('') ?: 'BT';
@endphp

<div class="card">
    <div class="top">
        <table class="top-table">
            <tr>
                <td class="top-info">
                    <div class="brand">BiblioTech</div>
                    <h1>{{ $membro->nome }}</h1>
                    <div class="email">{{ $membro->email }}</div>
                </td>
                <td class="photo-cell">
                    @if($photoData)
                        <img src="{{ $photoData }}" class="photo" alt="{{ $membro->nome }}">
                    @else
                        <div class="photo-fallback">{{ $initials }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="body">
        <div class="number">
            <div class="label">Número da carteirinha</div>
            <div class="card-number">{{ $membro->numero_carteirinha ?? 'Sem número' }}</div>
        </div>

        <div class="grid">
            <div class="row">
                <div class="cell">
                    <div class="label">CPF</div>
                    <div class="value">{{ $membro->cpf ?? 'Não informado' }}</div>
                </div>
                <div class="cell">
                    <div class="label">Telefone</div>
                    <div class="value">{{ $membro->telefone ?? 'Não informado' }}</div>
                </div>
            </div>
            <div class="row">
                <div class="cell">
                    <div class="label">Nascimento</div>
                    <div class="value">{{ $nascimento }}</div>
                </div>
                <div class="cell">
                    <div class="label">Perfil</div>
                    <div class="value">Membro</div>
                </div>
            </div>
            <div class="row">
                <div class="cell">
                    <div class="label">Situação</div>
                    <div class="value {{ $regular ? 'status-ok' : 'status-bad' }}">{{ $regular ? 'Regular' : 'Com pendência' }}</div>
                </div>
                <div class="cell">
                    <div class="label">Reservas ativas</div>
                    <div class="value">{{ $reservasAtivas }}</div>
                </div>
            </div>
        </div>

        <div class="footer">
            Gerado em {{ now()->format('d/m/Y \à\s H:i') }} &bull; Em uso: {{ $ativos }} &bull; Atrasos: {{ $atrasados }} &bull; Multas: R$ {{ number_format($multasPendentes, 2, ',', '.') }}
        </div>
    </div>
</div>
</body>
</html>
