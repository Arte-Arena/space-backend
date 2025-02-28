<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('pedidos_status')->insert([
            [
                'nome' => 'Em transporte',
                'fila' => 'E',
            ],
            [
                'nome' => 'Entregue',
                'fila' => 'E',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('status')->where('nome', 'Em transporte')->delete();
        DB::table('status')->where('nome', 'Entregue')->delete();
    }
};
