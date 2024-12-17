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
        Schema::create('octa_webhook', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable()->default(null);
            $table->string('telefone')->nullable()->default(null);
            $table->string('email')->nullable()->default(null);
            $table->string('origem')->nullable()->default(null);
            $table->string('url_octa')->nullable()->default(null);
            $table->string('octa_id')->nullable()->default(null);
            $table->text('primeira_mensagem_cliente')->nullable()->default(null);
            $table->string('responsavel_contato')->nullable()->default(null);
            $table->string('tel_comercial_contato')->nullable()->default(null);
            $table->string('tel_residencial_contato')->nullable()->default(null);
            $table->string('status_do_contato')->nullable()->default(null);
            $table->string('numero_de_pedido_contato')->nullable()->default(null);
            $table->string('nome_organizacao')->nullable()->default(null);
            $table->string('primeiro_telefone_organizacao')->nullable()->default(null);
            $table->string('primeiro_dominio_organizacao')->nullable()->default(null);
            $table->string('empresa')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('octa_webhook');
    }
};
