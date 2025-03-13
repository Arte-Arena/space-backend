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
        Schema::create('pedidos_arte_final_uniformes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_arte_final_id');
            $table->foreign('pedido_arte_final_id')->references('id')->on('pedidos_arte_final');
            $table->enum('esboco', [ 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' ]);
            $table->integer('quantidade_jogadores');
            $table->json('configuracoes');
            $table->timestamps();
            $table->unique(['pedido_arte_final_id', 'esboco']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_arte_final_uniformes');
    }
};
