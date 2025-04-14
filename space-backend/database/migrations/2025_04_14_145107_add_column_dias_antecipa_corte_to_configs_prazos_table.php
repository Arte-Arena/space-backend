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
        Schema::table('configs_prazos', function (Blueprint $table) {
            $table->integer('dias_antecipa_producao_confeccao_corte_conferencia')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configs_prazos', function (Blueprint $table) {
            $table->dropColumn('dias_antecipa_producao_confeccao_corte_conferencia');
        });
    }
};
