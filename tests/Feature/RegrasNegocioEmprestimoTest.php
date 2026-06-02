<?php

use App\Models\Emprestimos;
use App\Models\Livros;
use App\Models\Membros;
use App\Models\Pagamento;
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

it('bloqueia regularizacao de multa sem pagamento aprovado', function () {
    $gerente = User::factory()->create(['tipo_usuario' => 'gerente']);
    $membro = criarMembroParaRegra();
    $emprestimo = Emprestimos::create([
        'membro_id' => $membro->id,
        'livro_id' => criarLivroParaRegra()->id,
        'status' => Emprestimos::STATUS_DEVOLVIDO,
        'data_emprestimo' => now()->subDays(20)->toDateString(),
        'data_devolucao_prevista' => now()->subDays(5)->toDateString(),
        'data_devolucao_real' => now()->toDateString(),
        'valor_multa' => 5,
    ]);

    $this->actingAs($gerente)
        ->post(route('admin.emprestimos.regularizar-multa', $emprestimo))
        ->assertRedirect()
        ->assertSessionHas('erro', 'Esta multa ainda não possui pagamento aprovado. O membro precisa enviar o pagamento para conferência.');

    expect($emprestimo->fresh()->multa_paga_em)->toBeNull();
});

it('sincroniza regularizacao quando existe pagamento aprovado', function () {
    $gerente = User::factory()->create(['tipo_usuario' => 'gerente']);
    $membro = criarMembroParaRegra();
    $emprestimo = Emprestimos::create([
        'membro_id' => $membro->id,
        'livro_id' => criarLivroParaRegra()->id,
        'status' => Emprestimos::STATUS_DEVOLVIDO,
        'data_emprestimo' => now()->subDays(20)->toDateString(),
        'data_devolucao_prevista' => now()->subDays(5)->toDateString(),
        'data_devolucao_real' => now()->toDateString(),
        'valor_multa' => 5,
    ]);

    $pagoEm = now()->subHour();
    Pagamento::create([
        'membro_id' => $membro->id,
        'emprestimo_id' => $emprestimo->id,
        'codigo' => 'BT-PAY-TESTE-1',
        'metodo' => Pagamento::METODO_PIX,
        'status' => Pagamento::STATUS_APROVADO,
        'valor' => 5,
        'referencia' => 'MUL-' . str_pad((string) $emprestimo->id, 6, '0', STR_PAD_LEFT),
        'reviewed_by' => $gerente->id,
        'reviewed_at' => $pagoEm,
        'pago_em' => $pagoEm,
    ]);

    $this->actingAs($gerente)
        ->post(route('admin.emprestimos.regularizar-multa', $emprestimo))
        ->assertRedirect()
        ->assertSessionHas('sucesso', 'Multa regularizada com sucesso. O membro já pode solicitar novos empréstimos.');

    $emprestimo->refresh();
    expect($emprestimo->multa_paga_em?->timestamp)->toBe($pagoEm->timestamp);
    expect($emprestimo->multa_regularizada_por)->toBe($gerente->id);
});

it('registra evento quando aprova emprestimo', function () {
    $gerente = User::factory()->create(['tipo_usuario' => 'gerente']);
    $membro = criarMembroParaRegra();
    $livro = criarLivroParaRegra(['quantidade' => 1]);
    $solicitacao = Emprestimos::create([
        'membro_id' => $membro->id,
        'livro_id' => $livro->id,
        'status' => Emprestimos::STATUS_SOLICITADO,
        'data_emprestimo' => now()->toDateString(),
        'data_devolucao_prevista' => now()->addDays(14)->toDateString(),
        'valor_multa' => 0,
    ]);

    $this->actingAs($gerente)
        ->post(route('admin.emprestimos.aprovar', $solicitacao))
        ->assertRedirect()
        ->assertSessionHas('sucesso', 'Solicitação aprovada com sucesso.');

    $evento = $solicitacao->fresh()->eventos()->first();

    expect($evento?->evento)->toBe('emprestimo_aprovado');
    expect($evento?->user_id)->toBe($gerente->id);
});

it('permite membro cancelar solicitacao antes da aprovacao', function () {
    $membro = criarMembroParaRegra();
    $emprestimo = Emprestimos::create([
        'membro_id' => $membro->id,
        'livro_id' => criarLivroParaRegra()->id,
        'status' => Emprestimos::STATUS_SOLICITADO,
        'data_emprestimo' => now()->toDateString(),
        'data_devolucao_prevista' => now()->addDays(14)->toDateString(),
        'valor_multa' => 0,
    ]);

    $this->actingAs($membro, 'membro')
        ->post(route('emprestimos.cancelar-solicitacao', $emprestimo))
        ->assertRedirect()
        ->assertSessionHas('sucesso', 'Solicitação cancelada com sucesso.');

    $emprestimo->refresh();

    expect($emprestimo->status)->toBe(Emprestimos::STATUS_CANCELADO);
    expect($emprestimo->rejected_reason)->toBe('Cancelado pelo membro antes da aprovação.');
});

it('bloqueia cancelamento de solicitacao ja aprovada', function () {
    $membro = criarMembroParaRegra();
    $emprestimo = Emprestimos::create([
        'membro_id' => $membro->id,
        'livro_id' => criarLivroParaRegra()->id,
        'status' => Emprestimos::STATUS_APROVADO,
        'data_emprestimo' => now()->toDateString(),
        'data_devolucao_prevista' => now()->addDays(14)->toDateString(),
        'valor_multa' => 0,
    ]);

    $this->actingAs($membro, 'membro')
        ->post(route('emprestimos.cancelar-solicitacao', $emprestimo))
        ->assertRedirect()
        ->assertSessionHas('erro', 'Só é possível cancelar solicitações que ainda aguardam aprovação.');

    expect($emprestimo->fresh()->status)->toBe(Emprestimos::STATUS_APROVADO);
});

it('registra devolucao real pela data solicitada pelo membro', function () {
    $gerente = User::factory()->create(['tipo_usuario' => 'gerente']);
    $membro = criarMembroParaRegra();
    $livro = criarLivroParaRegra(['quantidade' => 0]);
    $dataSolicitada = now()->subDays(2)->startOfDay();

    $emprestimo = Emprestimos::create([
        'membro_id' => $membro->id,
        'livro_id' => $livro->id,
        'status' => Emprestimos::STATUS_DEVOLUCAO_SOLICITADA,
        'data_emprestimo' => now()->subDays(15)->toDateString(),
        'data_devolucao_prevista' => now()->subDays(4)->toDateString(),
        'return_requested_at' => $dataSolicitada,
        'valor_multa' => 0,
    ]);

    $this->actingAs($gerente)
        ->post(route('admin.emprestimos.devolver', $emprestimo))
        ->assertRedirect()
        ->assertSessionHas('sucesso');

    $emprestimo->refresh();

    expect($emprestimo->status)->toBe(Emprestimos::STATUS_DEVOLVIDO);
    expect($emprestimo->data_devolucao_real?->toDateString())->toBe($dataSolicitada->toDateString());
    expect((float) $emprestimo->valor_multa)->toBe(2.0);
});
