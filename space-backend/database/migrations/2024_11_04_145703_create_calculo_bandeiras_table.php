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
        Schema::create('calculo_bandeira', function (Blueprint $table) {
            $table->id();
            $table->decimal('altura', 8, 2);
            $table->decimal('largura', 8, 2);
            $table->decimal('custo_tecido', 10, 2);
            $table->decimal('custo_tinta', 10, 2);
            $table->decimal('custo_papel', 10, 2);
            $table->decimal('custo_imposto', 5, 2);
            $table->decimal('custo_final', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */    public function down(): void
    {
        Schema::dropIfExists('calculo_bandeira');
    }
};
