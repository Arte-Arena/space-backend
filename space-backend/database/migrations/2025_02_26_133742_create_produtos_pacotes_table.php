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
        Schema::create('produtos_pacotes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_de_tecido', ['Dryfit Liso', 'DryFit CambPro']);
            $table->boolean('permite_gola_customizada');
            $table->json('tipo_gola')->nullable();
            $table->boolean('permite_nome_de_jogador');
            $table->boolean('permite_escudo');
            $table->json('tipo_de_escudo_na_camisa')->nullable();
            $table->json('tipo_de_escudo_no_calcao')->nullable();
            $table->boolean('patrocinio_ilimitado');
            $table->integer('patrocinio_numero_maximo')->nullable();
            $table->json('tamanhos_permitidos')->nullable();
            $table->integer('numero_fator_protecao_uv_camisa');
            $table->integer('numero_fator_protecao_uv_calcao');
            $table->enum('tipo_de_tecido_meiao', ['Helanca Profissional', 'Helanca Profissional Premium']);
            $table->boolean('punho_personalizado');
            $table->boolean('etiqueta_de_produto_autentico');
            $table->boolean('logo_totem_em_patch_3d');
            $table->boolean('selo_de_produto_oficial');
            $table->boolean('selo_de_protecao_uv');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos_pacotes');
    }
};
