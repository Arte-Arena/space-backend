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
        Schema::create('pedidos_arte_final', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('SET NULL');
            $table->string('numero_pedido')->nullable();
            $table->date('prazo_arte_final')->nullable();
            $table->date('prazo_confeccao')->nullable();            
            $table->json('lista_produtos');
            $table->text('observacoes')->nullable();
            $table->string('rolo')->nullable();
            $table->foreignId('designer_id')->nullable()->constrained('users')->onDelete('SET NULL');
            $table->foreignId('pedido_status_id')->nullable()->constrained('pedidos_status')->onDelete('SET NULL');
            $table->foreignId('pedido_tipo_id')->nullable()->constrained('pedidos_tipos')->onDelete('SET NULL');
            $table->string('estagio')->nullable();
            $table->string('url_trello')->nullable();
            $table->text('situacao')->nullable();
            $table->string('prioridade')->nullable();
            $table->foreignId('orcamento_id')->nullable();
            $table->foreign('orcamento_id')->references('id')->on('orcamentos')->onDelete('SET NULL');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_arte_final');
    }
};
