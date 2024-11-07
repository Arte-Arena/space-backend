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
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Relacionamento com o usuário
            $table->float('altura');
            $table->float('largura');
            $table->float('custo_tecido');
            $table->float('custo_tinta');
            $table->float('custo_papel');
            $table->float('custo_imposto');
            $table->float('custo_final');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
    }
};
