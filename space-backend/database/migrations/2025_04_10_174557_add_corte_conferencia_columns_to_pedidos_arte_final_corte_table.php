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
        Schema::table('pedidos_arte_final_corte', function (Blueprint $table) {
            $table->string('status_corte')->nullable();
            $table->string('status_conferencia')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos_arte_final_corte', function (Blueprint $table) {
            $table->dropColumn('status_corte');
            $table->dropColumn('status_conferencia');
        });
    }
};
