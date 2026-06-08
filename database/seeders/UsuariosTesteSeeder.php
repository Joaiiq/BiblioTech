<?php

namespace Database\Seeders;

use App\Models\Emprestimos;
use App\Models\Livros;
use App\Models\Membros;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosTesteSeeder extends Seeder
{
    public function run(): void
    {
        $senha = Hash::make('12345678');

        for ($i = 1; $i <= 4; $i++) {
            User::updateOrCreate(
                ['email' => "bibliotecario{$i}@bibliotech.com"],
                [
                    'name' => "Bibliotecario {$i}",
                    'password' => $senha,
                    'tipo_usuario' => 'bibliotecario',
                ]
            );
        }

        $membros = [];
        for ($i = 1; $i <= 4; $i++) {
            $membros[$i] = Membros::updateOrCreate(
                ['cpf' => $this->cpfParaIndice($i)],
                [
                    'nome' => "Usuario {$i}",
                    'email' => "usuario{$i}@bibliotech.com",
                    'telefone' => "(85) 98888-000{$i}",
                    'endereco' => "Rua dos Testes, {$i}00",
                    'data_nascimento' => Carbon::create(2000, $i, 10)->toDateString(),
                    'tipo_membro' => 'membro',
                    'numero_carteirinha' => 'TST-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'password' => $senha,
                ]
            );
        }

        $livros = Livros::query()->orderBy('id')->take(4)->get();

        if ($livros->count() < 4) {
            return;
        }

        $this->criarEmprestimoEmDia($membros[1], $livros[0]);
        $this->criarEmprestimoDevendo($membros[2], $livros[1]);
        $this->criarMultaPendente($membros[2], $livros[2]);
    }

    private function criarEmprestimoEmDia(Membros $membro, Livros $livro): void
    {
        $retirada = Carbon::today()->subDays(2);

        Emprestimos::updateOrCreate(
            [
                'membro_id' => $membro->id,
                'livro_id' => $livro->id,
                'status' => Emprestimos::STATUS_EM_USO,
            ],
            [
                'data_emprestimo' => $retirada,
                'data_devolucao_prevista' => $retirada->copy()->addDays(Emprestimos::prazoDiasParaLivro($livro)),
                'data_devolucao_real' => null,
                'valor_multa' => 0,
            ]
        );
    }

    private function criarEmprestimoDevendo(Membros $membro, Livros $livro): void
    {
        $retirada = Carbon::today()->subDays(Emprestimos::prazoDiasParaLivro($livro) + 5);
        $dataPrevista = $retirada->copy()->addDays(Emprestimos::prazoDiasParaLivro($livro));

        Emprestimos::updateOrCreate(
            [
                'membro_id' => $membro->id,
                'livro_id' => $livro->id,
                'status' => Emprestimos::STATUS_EM_USO,
            ],
            [
                'data_emprestimo' => $retirada,
                'data_devolucao_prevista' => $dataPrevista,
                'data_devolucao_real' => null,
                'valor_multa' => 0,
            ]
        );
    }

    private function criarMultaPendente(Membros $membro, Livros $livro): void
    {
        $retirada = Carbon::today()->subDays(Emprestimos::prazoDiasParaLivro($livro) + 6);
        $dataPrevista = $retirada->copy()->addDays(Emprestimos::prazoDiasParaLivro($livro));
        $dataDevolucao = Carbon::today()->subDay();

        Emprestimos::updateOrCreate(
            [
                'membro_id' => $membro->id,
                'livro_id' => $livro->id,
                'status' => Emprestimos::STATUS_DEVOLVIDO,
            ],
            [
                'data_emprestimo' => $retirada,
                'data_devolucao_prevista' => $dataPrevista,
                'data_devolucao_real' => $dataDevolucao,
                'valor_multa' => Emprestimos::calcularMulta($dataPrevista, $dataDevolucao),
            ]
        );
    }

    private function cpfParaIndice(int $indice): string
    {
        return match ($indice) {
            1 => '529.982.247-25',
            2 => '111.444.777-35',
            3 => '935.411.347-80',
            default => '987.654.321-00',
        };
    }
}
