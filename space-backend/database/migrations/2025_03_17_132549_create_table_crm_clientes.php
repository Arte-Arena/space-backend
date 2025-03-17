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
        if (!Schema::hasTable('crm_clientes')) {
            Schema::create('crm_clientes', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->string('telefone', 30)->nullable();
                $table->string('email')->nullable();
                $table->string('origem', 50)->nullable();
                $table->string('url_octa', 100)->nullable();
                $table->string('primeira_mensagem_cliente')->nullable();
                $table->string('responsavel_contato')->nullable();
                $table->string('tel_comercial_contato')->nullable();
                $table->string('tel_residencial_contato')->nullable();
                $table->string('status_do_contato')->nullable();
                $table->string('numero_de_pedido_contato')->nullable();
                $table->string('nome_organizacao')->nullable();
                $table->string('primeiro_telefone_organizacao')->nullable();
                $table->string('primeiro_dominio_organizacao')->nullable();
                $table->string('empresa')->nullable();
                $table->dateTime('data_agendamento')->nullable();
                $table->string('status_conversa')->nullable();
                $table->string('mensagem_template_id')->nullable();
                $table->tinyInteger('contato_bloqueado')->nullable();
                $table->tinyInteger('contato_qualificado')->nullable();
                $table->string('motivo_perda')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_clientes');
    }
};
