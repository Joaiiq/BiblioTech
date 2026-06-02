<?php

use App\Models\Livros;
use App\Models\User;

it('mostra previa em pdf somente quando o livro possui arquivo', function () {
    $comPreview = Livros::factory()->create([
        'titulo' => 'Livro com prévia',
        'preview_pdf' => 'previews/livro-com-previa.pdf',
    ]);
    $semPreview = Livros::factory()->create([
        'titulo' => 'Livro sem prévia',
        'preview_pdf' => null,
    ]);

    $this->get(route('livros.show', $comPreview))
        ->assertOk()
        ->assertSee('Prévia em páginas')
        ->assertSee('previews/livro-com-previa.pdf');

    $this->get(route('livros.show', $semPreview))
        ->assertOk()
        ->assertDontSee('Prévia em páginas');
});

it('redireciona para o acervo quando exclui livro pela propria pagina', function () {
    $gerente = User::factory()->create(['tipo_usuario' => 'gerente']);
    $livro = Livros::factory()->create();

    $this->actingAs($gerente)
        ->from(route('livros.show', $livro))
        ->delete(route('livros.destroy', $livro))
        ->assertRedirect(route('livros.index', ['acervo' => 1]))
        ->assertSessionHas('sucesso', 'Livro removido com sucesso!');

    expect(Livros::withTrashed()->find($livro->id)?->trashed())->toBeTrue();
});
