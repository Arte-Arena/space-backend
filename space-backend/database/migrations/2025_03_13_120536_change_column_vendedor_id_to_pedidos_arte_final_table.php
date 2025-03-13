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
            $table->dropForeign(['vendedor_id']);
            $table->dropColumn('vendedor_id');
        });
        
        Schema::table('pedidos_arte_final', function (Blueprint $table) {
            $table->foreignId('vendedor_id')->nullable()->constrained('users')->onDelete('cascade')->unique();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos_arte_final', function (Blueprint $table) {
            $table->dropUnique(['vendedor_id']);
            $table->dropColumn('vendedor_id');
        });
    }
};
