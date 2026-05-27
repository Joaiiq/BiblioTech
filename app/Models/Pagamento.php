<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    use HasFactory;

    public const METODO_PIX = 'pix';
    public const METODO_CARTAO = 'cartao';
    public const METODO_SALDO = 'saldo_biblioteca';

    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_APROVADO = 'aprovado';
    public const STATUS_RECUSADO = 'recusado';

    protected $fillable = [
        'membro_id',
        'emprestimo_id',
        'codigo',
        'metodo',
        'status',
        'valor',
        'referencia',
        'metadata',
        'reviewed_by',
        'reviewed_at',
        'motivo_recusa',
        'pago_em',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'metadata' => 'array',
        'reviewed_at' => 'datetime',
        'pago_em' => 'datetime',
    ];

    public function membro()
    {
        return $this->belongsTo(Membros::class, 'membro_id');
    }

    public function emprestimo()
    {
        return $this->belongsTo(Emprestimos::class, 'emprestimo_id');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
