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
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('SET NULL');
            $table->string('cliente_octa_number')->default(1);
            $table->string('nome_cliente')->nullable()->default(null);
            $table->json('lista_produtos')->nullable()->default(null);
            $table->text('texto_orcamento')->nullable()->default(null);
            $table->string('endereco_cep')->default('');
            $table->string('endereco')->default('');
            $table->string('opcao_entrega')->default('');
            $table->integer('prazo_opcao_entrega')->default(0);
            $table->decimal('preco_opcao_entrega', 10, 2)->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orcamentos');
    }
};
