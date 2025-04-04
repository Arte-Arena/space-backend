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
        Schema::table('configs', function (Blueprint $table) {
            $table->integer('dias_antecipa_producao_arte_final')->nullable()->default(0);
            $table->integer('dias_antecipa_producao_impresao')->nullable()->default(0);
            $table->integer('dias_antecipa_producao_confeccao')->nullable()->default(0);
            $table->integer('dias_antecipa_producao_expedicao')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configs', function (Blueprint $table) {
            $table->dropColumn('dias_antecipa_producao_arte_final');
            $table->dropColumn('dias_antecipa_producao_impresao');
            $table->dropColumn('dias_antecipa_producao_confeccao');
            $table->dropColumn('dias_antecipa_producao_expedicao');
        });
    }
};
