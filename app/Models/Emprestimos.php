<?php

namespace App\Models;

use App\Notifications\ReservaDisponivel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Livros;
use App\Models\Membros;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
    public const STATUS_CANCELADO = 'cancelado';

    public const PRAZO_LIVRO_COMUM_DIAS = 14;
    public const PRAZO_BESTSELLER_DIAS = 7;
    public const VALOR_MULTA_DIARIA = 1.00;
    public const DIAS_ANTECEDENCIA_LEMBRETE = 2;
    public const DIAS_ANTECEDENCIA_RENOVACAO = 2;
    public const PRAZO_RETIRADA_DIAS = 3;
    public const MAX_RENOVACOES = 1;
    public const MAX_EMPRESTIMOS_ATIVOS = 3;

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
        'data_limite_retirada',
        'data_devolucao_real',
        'valor_multa',
        'multa_paga_em',
        'multa_regularizada_por',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_reason',
        'rejected_at',
        'return_requested_at',
        'renovacoes_count',
        'ultima_renovacao_em',
    ];

    protected $casts = [
        'data_emprestimo'         => 'date',
        'data_devolucao_prevista' => 'date',
        'data_limite_retirada'    => 'date',
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

    public static function possuiEmprestimoVencido(int $membroId, ?int $ignorarEmprestimoId = null): bool
    {
        return self::where('membro_id', $membroId)
            ->when($ignorarEmprestimoId, fn ($query) => $query->where('id', '!=', $ignorarEmprestimoId))
            ->whereIn('status', self::STATUS_EM_ANDAMENTO)
            ->where('data_devolucao_prevista', '<', now()->startOfDay())
            ->exists();
    }

    public static function totalAtivosDoMembro(int $membroId, ?int $ignorarEmprestimoId = null): int
    {
        return self::where('membro_id', $membroId)
            ->when($ignorarEmprestimoId, fn ($query) => $query->where('id', '!=', $ignorarEmprestimoId))
            ->whereIn('status', self::STATUS_ATIVOS)
            ->count();
    }

    public static function possuiLivroAtivo(int $membroId, int $livroId, ?int $ignorarEmprestimoId = null): bool
    {
        return self::where('membro_id', $membroId)
            ->where('livro_id', $livroId)
            ->when($ignorarEmprestimoId, fn ($query) => $query->where('id', '!=', $ignorarEmprestimoId))
            ->whereIn('status', self::STATUS_ATIVOS)
            ->exists();
    }

    public static function impedimentoParaNovoEmprestimo(int $membroId, ?int $livroId = null, ?int $ignorarEmprestimoId = null): ?string
    {
        if ($livroId && self::possuiLivroAtivo($membroId, $livroId, $ignorarEmprestimoId)) {
            return 'O membro já possui solicitação ou empréstimo ativo deste livro.';
        }

        if (self::totalAtivosDoMembro($membroId, $ignorarEmprestimoId) >= self::MAX_EMPRESTIMOS_ATIVOS) {
            return 'O membro atingiu o limite de 3 empréstimos ativos.';
        }

        if (self::possuiEmprestimoVencido($membroId, $ignorarEmprestimoId)) {
            return 'O membro possui empréstimos vencidos e precisa regularizar a situação.';
        }

        if (self::possuiMultaPendente($membroId)) {
            return 'O membro possui multas pendentes e precisa regularizar a situação.';
        }

        return null;
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
            && $this->data_devolucao_prevista !== null
            && now()->startOfDay()->diffInDays($this->data_devolucao_prevista, false) <= self::DIAS_ANTECEDENCIA_RENOVACAO;
    }

    public static function prazoLimiteRetirada(): \Carbon\Carbon
    {
        return now()->addDays(self::PRAZO_RETIRADA_DIAS)->startOfDay();
    }

    public static function expirarRetiradasPendentes(): int
    {
        $pendentes = self::with(['livro', 'membro'])
            ->where('status', self::STATUS_APROVADO)
            ->whereNotNull('data_limite_retirada')
            ->whereDate('data_limite_retirada', '<', today())
            ->get();

        if ($pendentes->isEmpty()) {
            return 0;
        }

        $pendentes->each(function (self $emprestimo): void {
            DB::transaction(function () use ($emprestimo): void {
                $emprestimo->update([
                    'status' => self::STATUS_CANCELADO,
                    'rejected_reason' => 'Retirada não realizada dentro do prazo.',
                    'rejected_at' => now(),
                ]);

                if ($emprestimo->livro) {
                    $emprestimo->livro->increment('quantidade');
                    self::atenderProximaReservaDaFila($emprestimo->livro->fresh());
                }

                $emprestimo->registrarEvento(
                    'retirada_expirada',
                    'Prazo de retirada expirado',
                    'O sistema cancelou a retirada porque o membro não buscou o exemplar no prazo.',
                    [
                        'limite_retirada' => $emprestimo->data_limite_retirada?->format('d/m/Y'),
                    ]
                );
            });
        });

        return $pendentes->count();
    }

    private static function atenderProximaReservaDaFila(?Livros $livro): void
    {
        if (!$livro || !Schema::hasTable('reservas') || (int) $livro->quantidade <= 0) {
            return;
        }

        $reserva = Reserva::with(['membro', 'livro'])
            ->ativas()
            ->where('livro_id', $livro->id)
            ->orderBy('created_at')
            ->first();

        if (!$reserva || !$reserva->membro) {
            return;
        }

        if (self::impedimentoParaNovoEmprestimo($reserva->membro_id, $reserva->livro_id)) {
            return;
        }

        $limiteRetirada = self::prazoLimiteRetirada();

        $emprestimo = self::create([
            'membro_id' => $reserva->membro_id,
            'livro_id' => $reserva->livro_id,
            'status' => self::STATUS_APROVADO,
            'data_emprestimo' => now()->startOfDay(),
            'data_devolucao_prevista' => $limiteRetirada,
            'data_limite_retirada' => $limiteRetirada,
            'data_devolucao_real' => null,
            'valor_multa' => 0,
            'approved_by' => null,
            'approved_at' => now(),
        ]);

        $livro->decrement('quantidade');
        $reserva->update(['status' => Reserva::STATUS_ATENDIDA]);

        $emprestimo->load('livro');
        $reserva->load('livro');

        $emprestimo->registrarEvento(
            'reserva_atendida',
            'Reserva atendida',
            'O sistema liberou a próxima reserva da fila após uma retirada expirar.',
            [
                'reserva_id' => $reserva->id,
                'origem' => 'fila_automatica',
            ]
        );

        $reserva->membro->notify(new ReservaDisponivel($reserva, $emprestimo));
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

    public function eventos()
    {
        return $this->hasMany(EmprestimoEvento::class, 'emprestimo_id')->latest();
    }

    public function pagamentoAprovado()
    {
        return $this->hasOne(Pagamento::class, 'emprestimo_id')
            ->where('status', Pagamento::STATUS_APROVADO)
            ->latestOfMany();
    }

    public function registrarEvento(string $evento, string $titulo, ?string $descricao = null, array $metadata = []): ?EmprestimoEvento
    {
        if (!Schema::hasTable('emprestimo_eventos')) {
            return null;
        }

        $userId = auth()->guard('web')->id();
        $membroId = auth()->guard('membro')->id();

        return $this->eventos()->create([
            'user_id' => $userId,
            'membro_id' => $membroId,
            'ator_tipo' => $userId ? 'admin' : ($membroId ? 'membro' : 'sistema'),
            'evento' => $evento,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'metadata' => $metadata ?: null,
        ]);
    }
}
