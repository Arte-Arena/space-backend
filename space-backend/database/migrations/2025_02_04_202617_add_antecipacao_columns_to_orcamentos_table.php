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
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->boolean('antecipado')->default(false);
            $table->date('data_antecipa')->nullable();
            $table->decimal('taxa_antecipa', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dropColumn('antecipado');
            $table->dropColumn('data_antecipa');
            $table->dropColumn('taxa_antecipa');
        });
    }
};
