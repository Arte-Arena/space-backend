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
        Schema::table('contas', function (Blueprint $table) {
            $table->json('parcelas')->nullable();
            $table->date('data_pagamento')->nullable(); // se foi paga
            $table->date('data_emissao')->nullable();   // emissão da conta
            $table->string('forma_pagamento')->nullable();
            $table->unsignedBigInteger('orcamento_staus_id')->nullable();
            $table->unsignedBigInteger('estoque_id')->nullable();
            $table->decimal('estoque_quantidade', 10, 2)->nullable();
            $table->string('recorrencia')->nullable(); // mensal trimestral ou numero de dias
            $table->boolean('fixa')->default(false); // se é uma conta fixa
            $table->string('documento')->nullable(); // nota, recibo
            $table->text('observacoes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas', function (Blueprint $table) {
            $table->dropColumn('parcelas');
            $table->dropColumn('data_pagamento');
            $table->dropColumn('data_emissao');
            $table->dropColumn('forma_pagamento');
            $table->dropColumn('orcamento_staus_id');
            $table->dropColumn('estoque_id');
            $table->dropColumn('estoque_quantidade');
            $table->dropColumn('recorrencia');
            $table->dropColumn('fixa');
            $table->dropColumn('documento');
            $table->dropColumn('observacoes');
        });
    }
};
