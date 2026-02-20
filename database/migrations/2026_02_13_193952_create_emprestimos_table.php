<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('emprestimos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membro_id')->constrained('membros'); 
            $table->foreignId('livro_id')->constrained('livros');
            $table->date('data_emprestimo');
            $table->date('data_devolucao_prevista'); // 7 ou 14 dias [1]
            $table->date('data_devolucao_real')->nullable();
            $table->decimal('valor_multa', 8, 2)->default(0); // RN003 [1]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emprestimos');
    }
};
