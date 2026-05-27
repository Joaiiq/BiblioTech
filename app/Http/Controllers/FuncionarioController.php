<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Puxando o model de Usuário
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash; // Para criptografar a senha
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FuncionarioController extends Controller
{
    public function index()
    {
        $bibliotecarios = User::whereIn('tipo_usuario', ['gerente', 'bibliotecario'])
            ->orderByRaw("FIELD(tipo_usuario, 'gerente', 'bibliotecario')")
            ->orderBy('name')
            ->get();

        return view('admin.bibliotecarios.index', compact('bibliotecarios'));
    }

    public function create()
    {
        return view('admin.bibliotecarios.create'); // A tela de cadastro de bibliotecário
    }
    public function store(Request $request)
    {
        // 1. Valida se o gerente preencheu tudo certo
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        // 2. Salva no banco de dados
        $bibliotecario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Criptografa a senha
            'tipo_usuario' => 'bibliotecario', 
        ]);
        AuditLog::record('bibliotecario_criado', "Cadastrou o bibliotecário {$bibliotecario->name}.", $bibliotecario, [
            'email' => $bibliotecario->email,
        ]);

        // 3. Devolve para a tela com uma mensagem de sucesso
        return redirect()->back()->with('sucesso', 'Bibliotecário cadastrado com sucesso!');
    }

    public function edit(User $bibliotecario)
    {
        if (!in_array($bibliotecario->tipo_usuario, ['gerente', 'bibliotecario'], true)) {
            abort(404);
        }

        return view('admin.bibliotecarios.edit', compact('bibliotecario'));
    }

    public function update(Request $request, User $bibliotecario)
    {
        if (!in_array($bibliotecario->tipo_usuario, ['gerente', 'bibliotecario'], true)) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($bibliotecario->id)],
            'password' => 'nullable|confirmed|min:6',
        ]);

        $bibliotecario->name = $validated['name'];
        $bibliotecario->email = $validated['email'];

        if (filled($validated['password'] ?? null)) {
            $bibliotecario->password = Hash::make($validated['password']);
        }

        $bibliotecario->save();
        AuditLog::record('bibliotecario_atualizado', "Atualizou o bibliotecário {$bibliotecario->name}.", $bibliotecario, [
            'email' => $bibliotecario->email,
        ]);

        return redirect()
            ->route('bibliotecarios.index')
            ->with('sucesso', 'Bibliotecário atualizado com sucesso!');
    }

    public function destroy(User $bibliotecario)
    {
        if ($bibliotecario->tipo_usuario !== 'bibliotecario') {
            return redirect()
                ->route('bibliotecarios.index')
                ->with('erro', 'Somente bibliotecários podem ser excluídos por aqui.');
        }

        if (auth()->id() === $bibliotecario->id) {
            return redirect()
                ->route('bibliotecarios.index')
                ->with('erro', 'Você não pode excluir sua própria conta.');
        }

        $nome = $bibliotecario->name;
        $email = $bibliotecario->email;

        if ($bibliotecario->profile_photo_path) {
            Storage::disk('public')->delete($bibliotecario->profile_photo_path);
        }

        $bibliotecario->delete();

        AuditLog::record('bibliotecario_excluido', "Excluiu o bibliotecário {$nome}.", null, [
            'email' => $email,
        ]);

        return redirect()
            ->route('bibliotecarios.index')
            ->with('sucesso', 'Bibliotecário excluído com sucesso!');
    }
}
