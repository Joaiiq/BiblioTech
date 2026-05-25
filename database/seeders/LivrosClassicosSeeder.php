<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Livros;
use App\Models\Autor;

class LivrosClassicosSeeder extends Seeder
{
    public function run(): void
    {
        // Lista de livros clássicos e autores
        $livros = [
            ['titulo' => 'Dom Casmurro', 'autor' => 'Machado de Assis'],
            ['titulo' => 'O Cortiço', 'autor' => 'Aluísio Azevedo'],
            ['titulo' => 'Memórias Póstumas de Brás Cubas', 'autor' => 'Machado de Assis'],
            ['titulo' => 'Iracema', 'autor' => 'José de Alencar'],
            ['titulo' => 'Senhora', 'autor' => 'José de Alencar'],
            ['titulo' => 'O Guarani', 'autor' => 'José de Alencar'],
            ['titulo' => 'Capitães da Areia', 'autor' => 'Jorge Amado'],
            ['titulo' => 'A Moreninha', 'autor' => 'Joaquim Manuel de Macedo'],
            ['titulo' => 'A Escrava Isaura', 'autor' => 'Bernardo Guimarães'],
            ['titulo' => 'Triste Fim de Policarpo Quaresma', 'autor' => 'Lima Barreto'],
        ];

        foreach ($livros as $info) {
            $autor = Autor::firstOrCreate(['nome' => $info['autor']]);
            $dados = Livros::factory()->make([
                'titulo' => $info['titulo'],
                'autor_id' => $autor->id,
                'categoria' => 'Romance',
            ])->toArray();

            Livros::firstOrCreate(
                [
                    'titulo' => $info['titulo'],
                    'autor_id' => $autor->id,
                ],
                $dados
            );
        }
    }
}
