<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livros', function (Blueprint $table) {
            if (!Schema::hasColumn('livros', 'preview_pdf')) {
                $table->string('preview_pdf')->nullable()->after('preview');
            }
        });
    }

    public function down(): void
    {
        Schema::table('livros', function (Blueprint $table) {
            if (Schema::hasColumn('livros', 'preview_pdf')) {
                $table->dropColumn('preview_pdf');
            }
        });
    }
};
