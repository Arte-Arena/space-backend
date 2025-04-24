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
        Schema::create('erros', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido')->nullable();
            $table->string('link_trello')->nullable();
            $table->string('setor')->nullable();
            $table->string('responsavel')->nullable();
            $table->decimal('prejuizo', 10, 2)->nullable();
            $table->string('detalhes')->nullable(); 
            $table->string('solucao')->nullable();  
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erros');
    }
};
