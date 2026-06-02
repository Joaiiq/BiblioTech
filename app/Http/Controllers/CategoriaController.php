<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Livros;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::orderBy('nome')
            ->withCount('livros')
            ->paginate(8)
            ->through(function (Categoria $categoria) {
                $categoria->livros_count = max($categoria->livros_count, Livros::where('categoria', $categoria->nome)->count());
                return $categoria;
            });

        return view('admin.categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:100', 'unique:categorias,nome'],
            'descricao' => ['nullable', 'string', 'max:500'],
        ]);

        Categoria::create($validated);

        return redirect()->back()->with('sucesso', 'Categoria cadastrada com sucesso.');
    }

    public function edit(Categoria $categoria)
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:100', Rule::unique('categorias', 'nome')->ignore($categoria->id)],
            'descricao' => ['nullable', 'string', 'max:500'],
        ]);

        $nomeAntigo = $categoria->nome;
        $categoria->update($validated);

        if ($nomeAntigo !== $categoria->nome) {
            Livros::where('categoria', $nomeAntigo)->update(['categoria' => $categoria->nome]);
        }

        return redirect()->route('categorias.index')->with('sucesso', 'Categoria atualizada com sucesso.');
    }
}
