<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório BiblioTech</title>
    <style>
        @page { margin: 24px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.35;
        }
        h1, h2, h3, p { margin: 0; }
        .header {
            border-bottom: 3px solid #1E3A8A;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .brand { color: #1E3A8A; font-size: 22px; font-weight: bold; }
        .brand span { color: #F59E0B; }
        .subtitle { color: #64748b; margin-top: 3px; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .metric {
            width: 25%;
            border: 1px solid #cbd5e1;
            padding: 10px;
            background: #f8fafc;
            vertical-align: top;
        }
        .metric-label {
            color: #64748b;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: .04em;
        }
        .metric-value {
            color: #0f172a;
            font-size: 17px;
            font-weight: bold;
            margin-top: 5px;
        }
        .section { margin-top: 16px; }
        .section-title {
            font-size: 13px;
            color: #1E3A8A;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        table.report {
            width: 100%;
            border-collapse: collapse;
        }
        table.report th {
            background: #dbeafe;
            color: #334155;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            padding: 7px;
            border: 1px solid #bfdbfe;
        }
        table.report td {
            padding: 7px;
            border: 1px solid #dbe3ef;
            vertical-align: top;
        }
        table.report tr:nth-child(even) td { background: #f8fafc; }
        .text-red { color: #dc2626; font-weight: bold; }
        .text-amber { color: #b45309; font-weight: bold; }
        .text-green { color: #047857; font-weight: bold; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">BIBLIO<span>TECH</span></div>
        <h1>Relatório Gerencial da Biblioteca</h1>
        <p class="subtitle">Período: {{ $inicio->format('d/m/Y') }} até {{ $fim->format('d/m/Y') }} · Emitido em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="grid">
        <tr>
            <td class="metric">
                <div class="metric-label">Livros no acervo</div>
                <div class="metric-value">{{ $metricas['livros'] }}</div>
            </td>
            <td class="metric">
                <div class="metric-label">Exemplares</div>
                <div class="metric-value">{{ $metricas['exemplares'] }}</div>
            </td>
            <td class="metric">
                <div class="metric-label">Empréstimos no período</div>
                <div class="metric-value">{{ $metricas['emprestimosPeriodo'] }}</div>
            </td>
            <td class="metric">
                <div class="metric-label">Multas registradas</div>
                <div class="metric-value">R$ {{ number_format($metricas['multas'], 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Situação atual</div>
        <table class="report">
            <thead>
                <tr>
                    <th>Em uso</th>
                    <th>Atrasados</th>
                    <th>Membros</th>
                    <th>Equipe cadastrada</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $metricas['ativos'] }}</td>
                    <td class="text-red">{{ $metricas['atrasados'] }}</td>
                    <td>{{ $metricas['membros'] }}</td>
                    <td>{{ $metricas['bibliotecarios'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Livros mais lidos</div>
        <table class="report">
            <thead>
                <tr>
                    <th>Livro</th>
                    <th>Categoria</th>
                    <th>Autor</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($livrosMaisLidos as $item)
                    <tr>
                        <td>{{ $item['livro']->titulo }}</td>
                        <td>{{ $item['livro']->categoria }}</td>
                        <td>{{ $item['livro']->autor->nome ?? 'Sem autor' }}</td>
                        <td class="text-amber">{{ $item['total'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Nenhum empréstimo encontrado no período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Perfis de leitores</div>
        <table class="report">
            <thead>
                <tr>
                    <th>Faixa etária</th>
                    <th>Perfil</th>
                    <th>Leitores</th>
                    <th>Empréstimos</th>
                    <th>Categoria preferida</th>
                </tr>
            </thead>
            <tbody>
                @forelse($perfisLeitores as $perfil)
                    <tr>
                        <td>{{ $perfil['faixa_etaria'] }}</td>
                        <td>{{ $perfil['tipo_membro'] }}</td>
                        <td>{{ $perfil['leitores'] }}</td>
                        <td class="text-amber">{{ $perfil['emprestimos'] }}</td>
                        <td>{{ $perfil['categoria_preferida'] }} <span class="muted">({{ $perfil['categoria_total'] }})</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Nenhum perfil de leitor encontrado no período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Sazonalidade por categoria</div>
        <table class="report">
            <thead>
                <tr>
                    <th>Mês</th>
                    <th>Categoria</th>
                    <th>Empréstimos</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sazonalidade as $item)
                    <tr>
                        <td>{{ $item['mes'] }}</td>
                        <td>{{ $item['categoria'] }}</td>
                        <td class="text-amber">{{ $item['total'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="muted">Nenhuma sazonalidade encontrada no período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Reservas em fila</div>
        <table class="report">
            <thead>
                <tr>
                    <th>Livro</th>
                    <th>Fila</th>
                    <th>Primeira reserva</th>
                    <th>Espera média</th>
                    <th>Membros na fila</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservasFila as $item)
                    <tr>
                        <td>
                            <strong>{{ $item['livro']->titulo }}</strong><br>
                            <span class="muted">{{ $item['livro']->categoria }} @if($item['livro']->autor) · {{ $item['livro']->autor->nome }} @endif</span>
                        </td>
                        <td class="text-amber">{{ $item['fila'] }}</td>
                        <td>{{ $item['primeira_reserva']?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $item['espera_media'] }} dias</td>
                        <td>{{ $item['membros']->join(', ') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Nenhuma reserva ativa em fila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Atrasos e multas previstas</div>
        <table class="report">
            <thead>
                <tr>
                    <th>Membro</th>
                    <th>Livro</th>
                    <th>Previsto</th>
                    <th>Dias em atraso</th>
                    <th>Multa prevista</th>
                </tr>
            </thead>
            <tbody>
                @forelse($atrasados as $emprestimo)
                    @php
                        $dias = (int) $emprestimo->data_devolucao_prevista->copy()->startOfDay()->diffInDays(now()->startOfDay());
                        $multa = \App\Models\Emprestimos::calcularMulta($emprestimo->data_devolucao_prevista);
                    @endphp
                    <tr>
                        <td>{{ $emprestimo->membro->nome ?? 'Membro removido' }}</td>
                        <td>{{ $emprestimo->livro->titulo ?? 'Livro removido' }}</td>
                        <td>{{ $emprestimo->data_devolucao_prevista?->format('d/m/Y') }}</td>
                        <td class="text-red">{{ $dias }}</td>
                        <td class="text-red">R$ {{ number_format($multa, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Nenhum empréstimo atrasado agora.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Sugestões de compra</div>
        <table class="report">
            <thead>
                <tr>
                    <th>Livro</th>
                    <th>Motivo</th>
                    <th>Prioridade</th>
                    <th>Reservas</th>
                    <th>Disponíveis</th>
                    <th>Circulação</th>
                    <th>Sugerido</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sugestoesCompra as $item)
                    @php
                        $priorityClass = match ($item['prioridade']) {
                            'Alta' => 'text-red',
                            'Média' => 'text-amber',
                            default => 'text-green',
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $item['livro']->titulo }}</strong><br>
                            <span class="muted">{{ $item['livro']->categoria }} @if($item['livro']->autor) · {{ $item['livro']->autor->nome }} @endif</span>
                        </td>
                        <td>{{ $item['motivo'] }}</td>
                        <td class="{{ $priorityClass }}">{{ $item['prioridade'] }}</td>
                        <td>{{ $item['reservas'] }}</td>
                        <td class="text-green">{{ $item['disponiveis'] }}</td>
                        <td class="text-amber">{{ $item['circulacao'] }}</td>
                        <td><strong>{{ $item['quantidade_sugerida'] }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Nenhuma sugestão de compra no período filtrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Acervo completo</div>
        <table class="report">
            <thead>
                <tr>
                    <th>Livro</th>
                    <th>Categoria</th>
                    <th>Autor</th>
                    <th>Estante</th>
                    <th>Total</th>
                    <th>Emprestados</th>
                    <th>Disponíveis</th>
                </tr>
            </thead>
            <tbody>
                @foreach($acervo as $item)
                    <tr>
                        <td>{{ $item['livro']->titulo }}</td>
                        <td>{{ $item['livro']->categoria }}</td>
                        <td>{{ $item['livro']->autor->nome ?? 'Sem autor' }}</td>
                        <td>{{ $item['livro']->estante ?: '—' }}</td>
                        <td>{{ $item['livro']->quantidade }}</td>
                        <td class="text-amber">{{ $item['emprestados'] }}</td>
                        <td class="text-green">{{ $item['disponiveis'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
