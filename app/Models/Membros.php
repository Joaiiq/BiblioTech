<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\User;

class Membros extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * We want the Membros model to behave like a normal User (using "name").
     *
     * To keep the database column as "nome" while allowing Breeze/Jetstream
     * and other code to use "name", we map name to nome via an accessor and
     * a mutator.
     *
     * Adding "name" to fillable makes mass assignment work when request uses
     * name (like the default Breeze register form).
     */
    protected $fillable = [
        'nome',
        'name',
        'email',
        'profile_photo_path',
        'cpf',
        'telefone',
        'endereco',
        'data_nascimento',
        'tipo_membro',
        'numero_carteirinha',
        'password',
    ];

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nome'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['nome'] = $value;
    }

    /**
     * Attributes hidden when serializing.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting definitions.
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function user()
    {
        // Aqui dizemos que este registro pertence ao Model User
        // O Laravel vai usar a coluna 'user_id' da sua tabela 'membros'
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'membro_id');
    }

    public function emprestimos()
    {
        return $this->hasMany(Emprestimos::class, 'membro_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'membro_id');
    }

    public function favoritos()
    {
        return $this->hasMany(Favorito::class, 'membro_id');
    }

    public function livrosFavoritos()
    {
        return $this->belongsToMany(Livros::class, 'favoritos', 'membro_id', 'livro_id')
            ->withTimestamps();
    }

}
