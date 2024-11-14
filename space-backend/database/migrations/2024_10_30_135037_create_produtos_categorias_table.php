<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produtos_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
        });

        DB::table('produtos_categorias')->insert([
            ['descricao' => 'Flamula'],
            ['descricao' => 'Abadá'],
            ['descricao' => 'Almofada'],
            ['descricao' => 'Bandana'],
            ['descricao' => 'Bandeira'],
            ['descricao' => 'Bandeira de Mesa'],
            ['descricao' => 'Bandeira de Carro'],
            ['descricao' => 'Bolachão'],
            ['descricao' => 'Boné'],
            ['descricao' => 'Braçadeira'],
            ['descricao' => 'Cachecol'],
            ['descricao' => 'Camiseta'],
            ['descricao' => 'Camisão'],
            ['descricao' => 'Canequinha Alumínio'],
            ['descricao' => 'Canequinha Porcelana'],
            ['descricao' => 'Capa de Corte'],
            ['descricao' => 'Chinelo'],
            ['descricao' => 'Cordão Chaveiro'],
            ['descricao' => 'Colet'],
            ['descricao' => 'Faixa'],
            ['descricao' => 'Faixa de Mão'],
            ['descricao' => 'Máscara'],
            ['descricao' => 'Roupão'],
            ['descricao' => 'Sacochila'],
            ['descricao' => 'Shorts'],
            ['descricao' => 'Shorts Doll'],
            ['descricao' => 'Tirante'],
            ['descricao' => 'Toalha'],
            ['descricao' => 'Uniforme'],
            ['descricao' => 'Windbanner'],
            ['descricao' => 'Calção'],
            ['descricao' => 'Estandarte'],
            ['descricao' => 'Samba Canção'],
            ['descricao' => 'Bandeira para Windbanner'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos_categorias');
    }
};
