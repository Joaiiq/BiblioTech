<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_livro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livro_id')->constrained('livros')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['livro_id', 'categoria_id']);
        });

        DB::table('livros')
            ->whereNotNull('categoria')
            ->orderBy('id')
            ->select(['id', 'categoria'])
            ->chunk(100, function ($livros) {
                foreach ($livros as $livro) {
                    $categoriaId = DB::table('categorias')->where('nome', $livro->categoria)->value('id');

                    if (! $categoriaId) {
                        $categoriaId = DB::table('categorias')->insertGetId([
                            'nome' => $livro->categoria,
                            'descricao' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('categoria_livro')->insertOrIgnore([
                        'livro_id' => $livro->id,
                        'categoria_id' => $categoriaId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_livro');
    }
};
