<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Membros; // mudei para usar o modelo de membros
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // ajuste de validação para assegurar unicidade na tabela
        // de membros e capturar pelo menos o nome (vai virar "nome")
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Membros::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Membros::create([
            'nome' => $request->name,
            'email' => $request->email,
            'cpf' => $this->gerarIdentificadorCadastro('cpf'),
            'telefone' => 'Não informado',
            'endereco' => 'Atualização pendente',
            'data_nascimento' => now()->subYears(18)->toDateString(),
            'tipo_membro' => 'membro',
            'numero_carteirinha' => $this->gerarNumeroCarteirinha(),
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // autentica usando o guard dos membros, que agora é padrão
        Auth::guard('membro')->login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function gerarNumeroCarteirinha(): string
    {
        do {
            $numero = 'BT-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        } while (Membros::where('numero_carteirinha', $numero)->exists());

        return $numero;
    }

    private function gerarIdentificadorCadastro(string $prefixo): string
    {
        do {
            $identificador = strtoupper($prefixo) . '-' . Str::upper(Str::random(12));
        } while (Membros::where($prefixo, $identificador)->exists());

        return $identificador;
    }
}
