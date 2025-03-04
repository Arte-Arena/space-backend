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
        Schema::table('orcamentos_status', function (Blueprint $table) {
            $table->date('data_faturamento_2')->nullable()->default(null);
            $table->date('data_faturamento_3')->nullable()->default(null);
            $table->decimal('valor_faturamento', 10, 2)->nullable()->default(null);
            $table->decimal('valor_faturamento_2', 10, 2)->nullable()->default(null);
            $table->decimal('valor_faturamento_3', 10, 2)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos_status', function (Blueprint $table) {
            $table->dropColumn('data_faturamento_2');
            $table->dropColumn('data_faturamento_3');
            $table->dropColumn('valor_faturamento');
            $table->dropColumn('valor_faturamento_2');
            $table->dropColumn('valor_faturamento_3');

        });
    }
};
