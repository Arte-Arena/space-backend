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
        Schema::table('pedidos_arte_final', function (Blueprint $table) {
            $table->dropForeign(['designer_id']);
            $table->dropColumn('designer_id');
        });

        Schema::table('pedidos_arte_final', function (Blueprint $table) {
            $table->foreignId('designer_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos_arte_final', function (Blueprint $table) {
            $table->dropColumn('designer_id');
        });
    }
};
