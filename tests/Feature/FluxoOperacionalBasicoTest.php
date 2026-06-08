<?php

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Livros;
use App\Models\Membros;
use App\Models\User;

it('redireciona cadastro de membro para o painel operacional com o membro selecionado', function () {
    $gerente = User::factory()->create(['tipo_usuario' => 'gerente']);

    $response = $this->actingAs($gerente)->post(route('membros.store'), [
        'nome' => 'Maria Leitora',
        'email' => 'maria.leitora@example.com',
        'cpf' => '52998224725',
        'telefone' => '85999999999',
        'endereco' => 'Rua das Flores, 123',
        'data_nascimento' => '2000-05-10',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $membro = Membros::where('email', 'maria.leitora@example.com')->firstOrFail();

    $response
        ->assertRedirect(route('admin.operacao.index', ['membro_id' => $membro->id]))
        ->assertSessionHas('sucesso');

    expect($membro->tipo_membro)->toBe('membro');
});

it('salva livro nacional no cadastro administrativo', function () {
    $bibliotecario = User::factory()->create(['tipo_usuario' => 'bibliotecario']);
    $autor = Autor::create(['nome' => 'Machado de Assis']);
    $categoria = Categoria::firstOrCreate(['nome' => 'Romance']);

    $this->actingAs($bibliotecario)->post(route('livros.store'), [
        'titulo' => 'Dom Casmurro',
        'autor_id' => $autor->id,
        'isbn' => '123-45-678-9012-3',
        'categorias' => [$categoria->id],
        'quantidade' => 3,
        'data_publicacao' => '1899-01-01',
        'sinopse' => 'Romance brasileiro.',
        'e_nacional' => '1',
    ])
        ->assertRedirect()
        ->assertSessionHas('sucesso');

    $livro = Livros::where('isbn', '123-45-678-9012-3')->firstOrFail();

    expect($livro->e_nacional)->toBeTrue();
    expect($livro->categorias()->count())->toBe(1);
});
