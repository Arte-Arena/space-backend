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
        Schema::create('pedidos_arte_final_confeccao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_arte_final_id')->unique()->constrained('pedidos_arte_final')->onDelete('cascade');
            $table->string('tipo_confeccao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_arte_final_confeccao');
    }
};
