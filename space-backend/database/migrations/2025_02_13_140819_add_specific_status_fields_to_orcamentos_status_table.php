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
        Schema::table('orcamentos_status', function (Blueprint $table) {
            $table->enum('status_aprovacao_arte_arena', ['aprovado', 'nao_aprovado'])->nullable();
            $table->enum('status_aprovacao_cliente', ['aguardando_aprovação', 'aprovado'])->nullable();
            $table->enum('status_envio_pedido', ['enviado', 'nao_enviado'])->nullable();
            $table->enum('status_aprovacao_amostra_arte_arena', ['aprovada', 'nao_aprovada'])->nullable();
            $table->enum('status_envio_amostra', ['enviada', 'nao_enviada'])->nullable();
            $table->enum('status_aprovacao_amostra_cliente', ['aprovada', 'nao_aprovada'])->nullable();
            $table->enum('status_faturamento', ['em_analise', 'faturado'])->nullable();
            $table->enum('status_pagamento', ['aguardando', 'pago'])->nullable();
            $table->enum('status_producao_esboco', ['aguardando_primeira_versao', 'aguardando_melhoria'])->nullable();
            $table->enum('status_producao_arte_final', ['aguardando_primeira_versao', 'aguardando_melhoria'])->nullable();
            $table->enum('status_aprovacao_esboco', ['aprovado', 'nao_aprovado'])->nullable();
            $table->enum('status_aprovacao_arte_final', ['aprovada', 'nao_aprovada'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos_status', function (Blueprint $table) {
            
            $table->dropColumn('status_aprovacao_arte_arena');
            $table->dropColumn('status_aprovacao_cliente');
            $table->dropColumn('status_envio_pedido');
            $table->dropColumn('status_aprovacao_amostra_arte_arena');
            $table->dropColumn('status_envio_amostra');
            $table->dropColumn('status_aprovacao_amostra_cliente');
            $table->dropColumn('status_faturamento');
            $table->dropColumn('status_pagamento');
            $table->dropColumn('status_producao_esboco');
            $table->dropColumn('status_producao_arte_final');
            $table->dropColumn('status_aprovacao_esboco');
            $table->dropColumn('status_aprovacao_arte_final');
        });
    }
};
