<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Livros;
use App\Models\Membros;
use Carbon\CarbonInterface;

class Emprestimos extends Model
{
    use HasFactory;

    public const STATUS_SOLICITADO = 'solicitado';
    public const STATUS_APROVADO = 'aprovado';
    public const STATUS_RETIRADO = 'retirado';
    public const STATUS_EM_USO = 'em_uso';
    public const STATUS_DEVOLUCAO_SOLICITADA = 'devolucao_solicitada';
    public const STATUS_DEVOLVIDO = 'devolvido';
    public const STATUS_ENCERRADO = 'encerrado';
    public const STATUS_REJEITADO = 'rejeitado';

    public const PRAZO_LIVRO_COMUM_DIAS = 14;
    public const PRAZO_BESTSELLER_DIAS = 7;
    public const VALOR_MULTA_DIARIA = 1.00;
    public const DIAS_ANTECEDENCIA_LEMBRETE = 2;
    public const MAX_RENOVACOES = 1;

    public const STATUS_ATIVOS = [
        self::STATUS_SOLICITADO,
        self::STATUS_APROVADO,
        self::STATUS_RETIRADO,
        self::STATUS_EM_USO,
        self::STATUS_DEVOLUCAO_SOLICITADA,
    ];

    public const STATUS_EM_ANDAMENTO = [
        self::STATUS_RETIRADO,
        self::STATUS_EM_USO,
        self::STATUS_DEVOLUCAO_SOLICITADA,
    ];

    protected $fillable = [
        'membro_id',
        'livro_id',
        'data_emprestimo',
        'status',
        'data_devolucao_prevista',
        'data_devolucao_real',
        'valor_multa',
        'multa_paga_em',
        'multa_regularizada_por',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_reason',
        'rejected_at',
        'renovacoes_count',
        'ultima_renovacao_em',
    ];

    protected $casts = [
        'data_emprestimo'         => 'date',
        'data_devolucao_prevista' => 'date',
        'data_devolucao_real'     => 'date',
        'status'                  => 'string',
        'approved_at'             => 'datetime',
        'rejected_at'             => 'datetime',
        'return_requested_at'     => 'datetime',
        'ultima_renovacao_em'     => 'datetime',
        'renovacoes_count'        => 'integer',
        'multa_paga_em'           => 'datetime',
    ];

    public function isAtrasado(): bool
    {
        if (!$this->data_devolucao_prevista) {
            return false;
        }

        return in_array($this->status, self::STATUS_EM_ANDAMENTO, true)
            && now()->startOfDay()->greaterThan($this->data_devolucao_prevista);
    }

    public static function prazoDiasParaLivro(?Livros $livro): int
    {
        return $livro?->e_bestseller
            ? self::PRAZO_BESTSELLER_DIAS
            : self::PRAZO_LIVRO_COMUM_DIAS;
    }

    public static function calcularMulta(?CarbonInterface $dataPrevista, ?CarbonInterface $dataDevolucao = null): float
    {
        if (!$dataPrevista) {
            return 0;
        }

        $dataDevolucao = ($dataDevolucao ?? now())->copy()->startOfDay();
        $dataPrevista = $dataPrevista->copy()->startOfDay();

        if (!$dataDevolucao->greaterThan($dataPrevista)) {
            return 0;
        }

        return (float) ((int) $dataPrevista->diffInDays($dataDevolucao) * self::VALOR_MULTA_DIARIA);
    }

    public static function possuiMultaPendente(int $membroId): bool
    {
        return self::where('membro_id', $membroId)
            ->where('status', self::STATUS_DEVOLVIDO)
            ->where('valor_multa', '>', 0)
            ->whereNull('multa_paga_em')
            ->exists();
    }

    public function multaPendente(): bool
    {
        return (float) $this->valor_multa > 0 && $this->multa_paga_em === null;
    }

    public function regularizadaPor()
    {
        return $this->belongsTo(User::class, 'multa_regularizada_por');
    }

    public function podeRenovar(): bool
    {
        return in_array($this->status, [self::STATUS_RETIRADO, self::STATUS_EM_USO], true)
            && !$this->isAtrasado()
            && (int) $this->renovacoes_count < self::MAX_RENOVACOES
            && $this->data_devolucao_prevista !== null;
    }

    // Relação 1: O Empréstimo tem um Livro
    public function livro()
    {
        return $this->belongsTo(Livros::class, 'livro_id');
    }

    // FALTAVA ISSO: O Empréstimo pertence a um Membro!
    public function membro()
    {
        return $this->belongsTo(Membros::class, 'membro_id');
    }

    public function aprovadoPor()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class, 'emprestimo_id');
    }

    public function pagamentoAprovado()
    {
        return $this->hasOne(Pagamento::class, 'emprestimo_id')
            ->where('status', Pagamento::STATUS_APROVADO)
            ->latestOfMany();
    }
}
