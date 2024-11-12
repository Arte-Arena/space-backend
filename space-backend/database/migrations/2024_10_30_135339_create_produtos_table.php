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
            $table->string('unidade')->nullable();
            $table->string('gtin')->nullable();
            $table->string('tipo_variacao')->nullable();
            $table->string('localizacao')->nullable();
            $table->decimal('preco_custo', 10, 2)->nullable();
            $table->decimal('preco_custo_medio', 10, 2)->nullable();
            $table->string('situacao')->nullable();
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
