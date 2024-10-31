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
        Schema::create('cadastros', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_pessoa', ['juridica', 'fisica']);
            $table->string('razao_social')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('ie')->nullable();
            $table->string('nome_completo')->nullable();
            $table->string('rg')->nullable();
            $table->string('cpf')->nullable();
            $table->string('email')->nullable(); // Campo de e-mail único para ambas as pessoas
            $table->string('endereco')->nullable();
            $table->string('cep')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('fone_fixo')->nullable();
            $table->string('cell')->nullable();
            $table->string('endereco_cobranca')->nullable();
            $table->string('cep_cobranca')->nullable();
            
            // Campos de endereço de entrega
            $table->string('endereco_entrega')->nullable();
            $table->string('cep_entrega')->nullable();
            $table->string('numero_entrega')->nullable();
            $table->string('bairro_entrega')->nullable();
            $table->string('cidade_entrega')->nullable();
            
            $table->string('responsavel_entrega')->nullable();
            $table->string('cpf_responsavel_entrega')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cadastros');
    }
};
