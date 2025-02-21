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
        Schema::create('orcamentos_status_etapa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('cascade')->unique();
            $table->enum('etapa', 
            [
                'status_aprovacao_arte_arena', 
                'status_aprovacao_cliente', 
                'status_envio_pedido', 
                'status_aprovacao_amostra_arte_arena', 
                'status_envio_amostra', 
                'status_aprovacao_amostra_cliente',
                'status_faturamento', 
                'status_pagamento', 
                'status_producao_esboco', 
                'status_producao_arte_final',
                'status_aprovacao_esboco', 
                'status_aprovacao_arte_final',
            ])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orcamentos_status_etapa');
    }
};
