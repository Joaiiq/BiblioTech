<?php

namespace Database\Factories;

use App\Models\Livros;
use Illuminate\Database\Eloquent\Factories\Factory;

class LivrosFactory extends Factory
{
    protected $model = Livros::class;

    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(3),
            'autor_id' => 1, // Ajuste conforme necessário
            'categoria' => 'Romance',
            'isbn' => $this->faker->isbn13(),
            'editora' => $this->faker->company(),
            'paginas' => $this->faker->numberBetween(100, 600),
            'quantidade' => $this->faker->numberBetween(1, 20),
            'data_publicacao' => $this->faker->date(),
            'capa' => null,
            'preview' => null,
            'preview_pdf' => null,
        ];
    }
}
