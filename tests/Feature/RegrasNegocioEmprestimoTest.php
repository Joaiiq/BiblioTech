<?php

use App\Models\Emprestimos;
use App\Models\Livros;
use App\Models\Membros;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function criarMembroParaRegra(array $attributes = []): Membros
{
    return Membros::create(array_merge([
        'nome' => 'Membro Teste',
        'email' => fake()->unique()->safeEmail(),
        'cpf' => fake()->unique()->numerify('###########'),
        'telefone' => '85999999999',
        'endereco' => 'Rua Teste, 123',
        'data_nascimento' => now()->subYears(20)->toDateString(),
        'tipo_membro' => 'estudante',
        'numero_carteirinha' => fake()->unique()->numerify('########'),
        'password' => Hash::make('password'),
    ], $attributes));
}

function criarLivroParaRegra(array $attributes = []): Livros
{
    return Livros::create(array_merge([
        'titulo' => fake()->sentence(3),
        'isbn' => fake()->unique()->isbn13(),
        'categoria' => 'Romance',
        'quantidade' => 2,
        'data_publicacao' => now()->subYear()->toDateString(),
    ], $attributes));
}

it('bloqueia aprovacao quando o membro tem emprestimo vencido', function () {
    $gerente = User::factory()->create(['tipo_usuario' => 'gerente']);
    $membro = criarMembroParaRegra();
    $livroVencido = criarLivroParaRegra();
    $livroSolicitado = criarLivroParaRegra(['quantidade' => 1]);

    Emprestimos::create([
        'membro_id' => $membro->id,
        'livro_id' => $livroVencido->id,
        'status' => Emprestimos::STATUS_EM_USO,
        'data_emprestimo' => now()->subDays(20)->toDateString(),
        'data_devolucao_prevista' => now()->subDay()->toDateString(),
        'valor_multa' => 0,
    ]);

    $solicitacao = Emprestimos::create([
        'membro_id' => $membro->id,
        'livro_id' => $livroSolicitado->id,
        'status' => Emprestimos::STATUS_SOLICITADO,
        'data_emprestimo' => now()->toDateString(),
        'data_devolucao_prevista' => now()->addDays(14)->toDateString(),
        'valor_multa' => 0,
    ]);

    $this->actingAs($gerente)
        ->post(route('admin.emprestimos.aprovar', $solicitacao))
        ->assertRedirect()
        ->assertSessionHas('erro', 'O membro possui empréstimos vencidos e precisa regularizar a situação.');

    expect($solicitacao->fresh()->status)->toBe(Emprestimos::STATUS_SOLICITADO);
    expect($livroSolicitado->fresh()->quantidade)->toBe(1);
});

it('bloqueia aprovacao acima do limite de emprestimos ativos', function () {
    $gerente = User::factory()->create(['tipo_usuario' => 'gerente']);
    $membro = criarMembroParaRegra();

    foreach (range(1, Emprestimos::MAX_EMPRESTIMOS_ATIVOS) as $index) {
        Emprestimos::create([
            'membro_id' => $membro->id,
            'livro_id' => criarLivroParaRegra()->id,
            'status' => Emprestimos::STATUS_EM_USO,
            'data_emprestimo' => now()->subDays($index)->toDateString(),
            'data_devolucao_prevista' => now()->addDays($index)->toDateString(),
            'valor_multa' => 0,
        ]);
    }

    $solicitacao = Emprestimos::create([
        'membro_id' => $membro->id,
        'livro_id' => criarLivroParaRegra(['quantidade' => 1])->id,
        'status' => Emprestimos::STATUS_SOLICITADO,
        'data_emprestimo' => now()->toDateString(),
        'data_devolucao_prevista' => now()->addDays(14)->toDateString(),
        'valor_multa' => 0,
    ]);

    $this->actingAs($gerente)
        ->post(route('admin.emprestimos.aprovar', $solicitacao))
        ->assertRedirect()
        ->assertSessionHas('erro', 'O membro atingiu o limite de 3 empréstimos ativos.');

    expect($solicitacao->fresh()->status)->toBe(Emprestimos::STATUS_SOLICITADO);
});
