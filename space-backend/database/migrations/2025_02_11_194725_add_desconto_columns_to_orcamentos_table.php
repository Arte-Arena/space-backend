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
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->boolean('descontado')->default(false);
            $table->enum('tipo_desconto', ['percentual', 'valor'])->nullable();
            $table->decimal('valor_desconto', 10, 2)->nullable();
            $table->decimal('percentual_desconto', 10, 2)->nullable();
            $table->decimal('total_orcamento', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dropColumn('descontado');
            $table->dropColumn('tipo_desconto');
            $table->dropColumn('valor_desconto');
            $table->dropColumn('percentual_desconto');
            $table->dropColumn('total_orcamento');
        });
    }
};
