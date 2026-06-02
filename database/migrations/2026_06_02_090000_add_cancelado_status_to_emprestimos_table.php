<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE emprestimos MODIFY status ENUM('solicitado','aprovado','retirado','em_uso','devolucao_solicitada','devolvido','encerrado','rejeitado','cancelado') NOT NULL DEFAULT 'solicitado'");
    }

    public function down(): void
    {
        if (Schema::hasTable('emprestimos')) {
            DB::table('emprestimos')
                ->where('status', 'cancelado')
                ->update(['status' => 'rejeitado']);
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE emprestimos MODIFY status ENUM('solicitado','aprovado','retirado','em_uso','devolucao_solicitada','devolvido','encerrado','rejeitado') NOT NULL DEFAULT 'solicitado'");
    }
};
