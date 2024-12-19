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
        Schema::create('produtos_personalizad', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique()->nullable(false)->index();
            $table->decimal('preco', 10, 2)->nullable()->default(null);
            $table->integer('prazo')->nullable()->default(null);
            $table->decimal('peso', 10, 2)->nullable()->default(null);
            $table->decimal('largura', 10, 2)->nullable()->default(null);
            $table->decimal('altura', 10, 2)->nullable()->default(null);
            $table->decimal('comprimento', 10, 2)->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos_personalizad');
    }
};
