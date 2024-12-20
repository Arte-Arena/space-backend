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
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente');
            $table->unsignedBigInteger('aprovado_por')->nullable(); // ID do usuário que aprovou
            $table->timestamp('aprovado_em')->nullable(); // Data e hora da aprovação

            $table->foreign('aprovado_por')->references('id')->on('users')->nullable(); // Chave estrangeira para usuários
 
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
