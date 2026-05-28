<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmprestimoEvento extends Model
{
    protected $fillable = [
        'emprestimo_id',
        'user_id',
        'membro_id',
        'ator_tipo',
        'evento',
        'titulo',
        'descricao',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function emprestimo(): BelongsTo
    {
        return $this->belongsTo(Emprestimos::class, 'emprestimo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membros::class, 'membro_id');
    }

    public function getAtorNomeAttribute(): string
    {
        return $this->user?->name
            ?? $this->membro?->nome
            ?? 'Sistema';
    }
}
