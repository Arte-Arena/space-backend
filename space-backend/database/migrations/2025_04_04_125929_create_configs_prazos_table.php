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
        Schema::create('configs_prazos', function (Blueprint $table) {
            $table->id();
            $table->integer('dias_antecipa_producao_arte_final')->nullable()->default(0);
            $table->integer('dias_antecipa_producao_impressao')->nullable()->default(0);
            $table->integer('dias_antecipa_producao_confeccao_costura')->nullable()->default(0);
            $table->integer('dias_antecipa_producao_confeccao_sublimacao')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs_prazos');
    }
};
