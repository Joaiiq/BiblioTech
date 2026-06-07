<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Autor;
use App\Rules\RealisticDate;

class AutorController extends Controller
{
    public function index()
    {
        $busca = trim((string) request('busca', ''));
        $nacionalidade = request('nacionalidade', 'todas');
        $ordem = request('ordem', 'nome');

        $baseQuery = Autor::query()->withCount('livros');

        $autores = (clone $baseQuery)
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($subquery) use ($busca) {
                    $subquery
                        ->where('nome', 'like', "%{$busca}%")
                        ->orWhere('nacionalidade', 'like', "%{$busca}%")
                        ->orWhere('biografia', 'like', "%{$busca}%");
                });
            })
            ->when($nacionalidade !== 'todas', fn ($query) => $query->where('nacionalidade', $nacionalidade))
            ->when($ordem === 'mais_livros', fn ($query) => $query->orderByDesc('livros_count')->orderBy('nome'))
            ->when($ordem === 'recentes', fn ($query) => $query->latest())
            ->when($ordem === 'nome', fn ($query) => $query->orderBy('nome'))
            ->paginate(12)
            ->withQueryString();

        $nacionalidades = Autor::query()
            ->whereNotNull('nacionalidade')
            ->where('nacionalidade', '!=', '')
            ->orderBy('nacionalidade')
            ->distinct()
            ->pluck('nacionalidade');

        $metricas = [
            'total_autores' => Autor::count(),
            'com_livros' => Autor::has('livros')->count(),
            'sem_livros' => Autor::doesntHave('livros')->count(),
            'total_livros_vinculados' => (int) Autor::withCount('livros')->get()->sum('livros_count'),
        ];

        return view('admin.autores.index', compact(
            'autores',
            'busca',
            'nacionalidade',
            'nacionalidades',
            'ordem',
            'metricas',
        ));
    }

    public function create()
    {
        return view('admin.autores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'biografia' => 'nullable|string',
            'data_nascimento' => ['nullable', 'date_format:Y-m-d', new RealisticDate('author_birth')],
            'nacionalidade' => 'nullable|string|max:255',
        ]);

        $dados = $request->all();

        if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
            $dados['foto'] = $request->file('foto')->store('autores', 'public');
        }

        $autor = Autor::create($dados);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Autor cadastrado com sucesso.',
                'autor' => [
                    'id' => $autor->id,
                    'nome' => $autor->nome,
                ],
            ], 201);
        }

        return redirect()->route('autores.index')->with('sucesso', 'Autor cadastrado com sucesso!');
    }

    public function show($id)
    {
        $autor = Autor::with(['livros' => fn ($query) => $query->latest()])->findOrFail($id);
        return view('autores.show', compact('autor'));
    }

    public function edit($id)
    {
        $autor = Autor::findOrFail($id);
        return view('admin.autores.edit', compact('autor'));
    }

    public function update(Request $request, $id)
    {
        $autor = Autor::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'biografia' => 'nullable|string',
            'data_nascimento' => ['nullable', 'date_format:Y-m-d', new RealisticDate('author_birth')],
            'nacionalidade' => 'nullable|string|max:255',
        ]);

        $dados = $request->all();

        if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
            if ($autor->foto) {
                Storage::disk('public')->delete($autor->foto);
            }
            $dados['foto'] = $request->file('foto')->store('autores', 'public');
        }

        $autor->update($dados);

        return redirect()->route('autores.index')->with('sucesso', 'Autor atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $autor = Autor::withCount('livros')->findOrFail($id);

        if ($autor->livros_count > 0) {
            return redirect()
                ->route('autores.index')
                ->with('erro', 'Este autor ainda possui livros vinculados. Remova ou altere os livros antes de excluir.');
        }

        if ($autor->foto) {
            Storage::disk('public')->delete($autor->foto);
        }

        $autor->delete();

        return redirect()->route('autores.index')->with('sucesso', 'Autor removido com sucesso!');
    }
}
