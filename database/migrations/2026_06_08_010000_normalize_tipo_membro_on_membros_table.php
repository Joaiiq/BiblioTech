<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('membros')->update(['tipo_membro' => 'membro']);
    }

    public function down(): void
    {
        //
    }
};
