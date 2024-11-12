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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('SET NULL');
            $table->string('numero_pedido')->nullable();
            $table->dateTime('data_prevista')->nullable();
            $table->string('pedido_produto_categoria')->nullable();
            $table->string('pedido_material')->nullable();
            $table->decimal('medida_linear', 10, 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->string('rolo')->nullable();
            $table->foreignId('designer_id')->constrained('users')->onDelete('SET NULL');
            $table->foreignId('pedido_status_id')->constrained('pedidos_status')->onDelete('SET NULL');
            $table->foreignId('pedido_tipo_id')->constrained('pedidos_tipos')->onDelete('SET NULL');
            $table->string('estagio')->nullable();
            $table->string('url_trello')->nullable();
            $table->text('situacao')->nullable();
            $table->string('prioridade')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
