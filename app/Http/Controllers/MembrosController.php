<?php

namespace App\Http\Controllers;

use App\Rules\ValidCpf;
use App\Rules\ValidPhonePrefix;
use App\Rules\RealisticDate;
use App\Models\Membros;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Models\User; // Para criar o usuário associado ao membro
use Illuminate\Validation\Rule;

class MembrosController extends Controller
{
    public function create()
    {
        return view('membros.create'); 

    }

    public function edit(Membros $membro)
    {
        return view('membros.edit', compact('membro'));
    }

    public function store(Request $request)
    {
        // Validação dos dados
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:membros,email',
            'cpf' => ['required', 'string', 'unique:membros,cpf', new ValidCpf],
            'telefone' => ['required', 'string', 'max:20', new ValidPhonePrefix],
            'endereco' => 'required|string|max:255',
            'data_nascimento' => ['required', 'date_format:Y-m-d', new RealisticDate('member_birth')],
            'tipo_membro' => 'required|string|max:50',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $numeroCarteirinha = $this->gerarNumeroCarteirinha();

        // Salvar no banco de dados (senha já será criptografada pelo cast do modelo)
        $membro = Membros::create([
            'user_id' => $request->id, // Associa o membro ao usuário criado
            'nome' => $request->nome,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'telefone' => $request->telefone,
            'endereco' => $request->endereco,
            'data_nascimento' => $request->data_nascimento,
            'tipo_membro' => $request->tipo_membro,
            'numero_carteirinha' => $numeroCarteirinha,
            'password' => $request->password,
        ]);
        AuditLog::record('membro_criado', "Cadastrou o membro {$membro->nome}.", $membro, [
            'carteirinha' => $numeroCarteirinha,
            'email' => $membro->email,
        ]);

        return redirect()
            ->route('admin.operacao.index', ['membro_id' => $membro->id])
            ->with('sucesso', 'Membro cadastrado com sucesso! Carteirinha gerada: ' . $numeroCarteirinha);
    }

    public function update(Request $request, Membros $membro)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('membros', 'email')->ignore($membro->id),
            ],
            'cpf' => [
                'required',
                'string',
                Rule::unique('membros', 'cpf')->ignore($membro->id),
                new ValidCpf,
            ],
            'telefone' => ['required', 'string', 'max:20', new ValidPhonePrefix],
            'endereco' => 'required|string|max:255',
            'data_nascimento' => ['required', 'date_format:Y-m-d', new RealisticDate('member_birth')],
            'tipo_membro' => 'required|string|max:50',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $membro->update($validated);
        AuditLog::record('membro_atualizado', "Atualizou os dados do membro {$membro->nome}.", $membro, [
            'carteirinha' => $membro->numero_carteirinha,
            'email' => $membro->email,
        ]);

        return redirect()
            ->route('admin.membros.show', $membro)
            ->with('sucesso', 'Dados do membro atualizados com sucesso.');
    }

    private function gerarNumeroCarteirinha(): string
    {
        $maiorNumero = Membros::where('numero_carteirinha', 'like', 'BT-%')
            ->get()
            ->map(function (Membros $membro) {
                return (int) preg_replace('/\D/', '', $membro->numero_carteirinha);
            })
            ->max() ?? 0;

        $proximo = $maiorNumero + 1;

        return 'BT-' . str_pad((string) $proximo, 6, '0', STR_PAD_LEFT);
    }
}
