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
        Schema::create('categoria_produtos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
        });

        DB::table('categoria_produtos')->insert([
            ['descricao' => 'FLAMULA'],
            ['descricao' => 'ABADÁ'],
            ['descricao' => 'ALMOFADA'],
            ['descricao' => 'BANDANA'],
            ['descricao' => 'BANDEIRA'],
            ['descricao' => 'BANDEIRA DE MESA'],
            ['descricao' => 'BANDEIRA DE CARRO'],
            ['descricao' => 'BOLACHÃO'],
            ['descricao' => 'BONÉ'],
            ['descricao' => 'BRAÇADEIRA'],
            ['descricao' => 'CACHECOL'],
            ['descricao' => 'CAMISETA'],
            ['descricao' => 'CAMISÃO'],
            ['descricao' => 'CANECA ALUMINÍO'],
            ['descricao' => 'CANECA PORCELANA'],
            ['descricao' => 'CAPA DE CORTE'],
            ['descricao' => 'CHINELO'],
            ['descricao' => 'CORDÃO CHAVEIRO'],
            ['descricao' => 'COLETE'],
            ['descricao' => 'FAIXA'],
            ['descricao' => 'FAIXA DE MÃO'],
            ['descricao' => 'MÁSCARA'],
            ['descricao' => 'ROUPÃO'],
            ['descricao' => 'SACOCHILA'],
            ['descricao' => 'SHORTS'],
            ['descricao' => 'SHORTS DOLL'],
            ['descricao' => 'TIRANTE'],
            ['descricao' => 'TOALHA'],
            ['descricao' => 'UNIFORME'],
            ['descricao' => 'WINDBANNER'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_produtos');
    }
};
