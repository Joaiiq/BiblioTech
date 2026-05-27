<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membro_id')->constrained('membros')->cascadeOnDelete();
            $table->foreignId('emprestimo_id')->constrained('emprestimos')->cascadeOnDelete();
            $table->string('codigo')->unique();
            $table->enum('metodo', ['pix', 'cartao', 'saldo_biblioteca']);
            $table->enum('status', ['pendente', 'aprovado', 'recusado'])->default('pendente');
            $table->decimal('valor', 8, 2);
            $table->string('referencia')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('pago_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
