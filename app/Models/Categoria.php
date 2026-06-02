<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
    ];

    public function livros()
    {
        return $this->belongsToMany(Livros::class, 'categoria_livro', 'categoria_id', 'livro_id')
            ->withTimestamps();
    }

    public static function nomesDisponiveis()
    {
        if (!Schema::hasTable('categorias')) {
            return collect(Livros::CATEGORIAS);
        }

        $categorias = self::orderBy('nome')->pluck('nome');

        return $categorias->isNotEmpty()
            ? $categorias
            : collect(Livros::CATEGORIAS);
    }
}
