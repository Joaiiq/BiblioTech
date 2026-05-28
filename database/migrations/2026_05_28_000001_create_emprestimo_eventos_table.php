<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emprestimo_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emprestimo_id')->constrained('emprestimos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('membro_id')->nullable()->constrained('membros')->nullOnDelete();
            $table->string('ator_tipo')->nullable();
            $table->string('evento');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['emprestimo_id', 'created_at']);
            $table->index('evento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprestimo_eventos');
    }
};
