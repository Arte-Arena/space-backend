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
        Schema::create('contas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('SET NULL');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->decimal('valor', 8, 2);
            $table->date('data_vencimento');
            $table->enum('status', ['pago', 'pendente', 'recebido'])->default('pendente');
            $table->enum('tipo', ['a pagar', 'a receber'])->default('a pagar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas');
    }
};
