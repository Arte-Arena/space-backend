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
        Schema::create('clientes_cadastro_short_urls', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->unsignedBigInteger('orcamento_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes_cadastro_short_urls');
    }
};
