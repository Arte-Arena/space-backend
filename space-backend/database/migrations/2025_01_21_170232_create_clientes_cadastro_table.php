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
        Schema::create('clientes_cadastro', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_pessoa', ['PJ', 'PF'])->default('PF');
            $table->string('nome_completo')->nullable();
            $table->string('rg')->nullable();
            $table->string('cpf')->nullable();
            $table->string('email')->nullable();
            $table->string('celular')->nullable();
            $table->string('cep')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();

            // Campos para Pessoa Jurídica
            $table->string('razao_social')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('inscricao_estadual')->nullable();

            // Campos de Cobrança
            $table->string('cep_cobranca')->nullable();
            $table->string('endereco_cobranca')->nullable();
            $table->string('numero_cobranca')->nullable();
            $table->string('complemento_cobranca')->nullable();
            $table->string('bairro_cobranca')->nullable();
            $table->string('cidade_cobranca')->nullable();
            $table->string('uf_cobranca', 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes_cadastro');
    }
};
