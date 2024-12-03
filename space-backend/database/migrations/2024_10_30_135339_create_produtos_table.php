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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('codigo')->nullable();
            $table->decimal('preco', 10, 2)->nullable();
            $table->decimal('preco_promocional', 10, 2)->nullable();
            $table->decimal('preco_custo', 10, 2)->nullable();
            $table->decimal('preco_custo_medio', 10, 2)->nullable();
            $table->decimal('peso_liquido', 10, 2)->nullable();
            $table->decimal('peso_bruto', 10, 2)->nullable();            
            $table->string('tipoEmbalagem')->nullable();
            $table->decimal('alturaEmbalagem', 10, 2)->nullable();
            $table->decimal('comprimentoEmbalagem', 10, 2)->nullable();
            $table->decimal('larguraEmbalagem', 10, 2)->nullable();
            $table->decimal('diametroEmbalagem', 10, 2)->nullable();
            $table->string('unidade')->nullable();
            $table->string('gtin')->nullable();
            $table->string('gtin_embalagem')->nullable();
            $table->string('localizacao')->nullable();
            $table->string('situacao')->nullable();
            $table->string('tipo')->nullable();
            $table->string('tipo_variacao')->nullable();
            $table->string('ncm')->nullable();
            $table->string('origem')->nullable();
            $table->decimal('estoque_minimo', 10, 2)->nullable();
            $table->decimal('estoque_maximo', 10, 2)->nullable();
            $table->unsignedBigInteger('id_fornecedor')->nullable();
            $table->string('nome_fornecedor')->nullable();
            $table->string('codigo_fornecedor')->nullable();
            $table->string('codigo_pelo_fornecedor')->nullable();
            $table->string('unidade_por_caixa')->nullable();
            $table->string('classe_ipi')->nullable();
            $table->decimal('valor_ipi_fixo', 10, 2)->nullable();
            $table->string('cod_lista_servicos')->nullable();
            $table->text('descricao_complementar')->nullable();
            $table->string('garantia')->nullable();
            $table->string('cest')->nullable();
            $table->string('obs')->nullable();
            $table->string('tipoVariacao')->nullable();
            $table->string('variacoes')->nullable();
            $table->unsignedBigInteger('idProdutoPai')->nullable();
            $table->string('sob_encomenda')->nullable();
            $table->integer('dias_preparacao')->nullable();
            $table->string('marca')->nullable();
            $table->integer('qtd_volumes')->nullable();
            $table->string('categoria')->nullable();
            $table->json('anexos')->nullable();
            $table->json('imagens_externas')->nullable();
            $table->string('classe_produto')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('link_video')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
