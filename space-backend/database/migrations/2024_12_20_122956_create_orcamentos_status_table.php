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
        Schema::create('orcamentos_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('SET NULL');
            $table->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('cascade')->unique();
            $table->enum('status', ['aprovado', 'pendente'])->nullable()->default(null);
            $table->string('forma_pagamento')->nullable()->default(null);
            $table->string('tipo_faturamento')->nullable()->default(null);
            $table->date('data_faturamento')->nullable()->default(null);
            $table->integer('qtd_parcelas')->nullable()->default(null);
            $table->string('link_trello')->nullable()->default(null);
            $table->date('data_entrega')->nullable()->default(null);
            $table->text('comentarios')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orcamentos_status');
    }
};
