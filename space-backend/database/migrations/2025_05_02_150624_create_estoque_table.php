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
        Schema::create('estoque', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->text('descricao')->nullable();
            $table->json('variacoes')->nullable();
            $table->string('unidade_medida')->nullable();
            $table->integer('quantidade')->nullable();
            $table->integer('estoque_min')->nullable();
            $table->integer('estoque_max')->nullable();
            $table->string('categoria')->nullable();
            $table->json('fornecedores')->nullable();
            $table->unsignedBigInteger('produto_id')->nullable();
            $table->string('produto_table')->nullable();
            $table->timestamps();

            $table->index(['produto_id', 'produto_table']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoque');
    }
};
