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
        Schema::create('membros', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('cpf')->unique();
            $table->string('telefone');
            $table->text('endereco');
            $table->date('data_nascimento');
            $table->string('tipo_membro'); // Estudante, Professor, etc.
            $table->string('numero_carteirinha')->unique(); // RF001 [2]
            $table->timestamps();
            $table->softDeletes(); // Para integridade de dados [3, 4]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membros');
    }
};
