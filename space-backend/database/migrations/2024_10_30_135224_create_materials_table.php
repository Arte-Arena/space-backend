<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descricao');
            $table->timestamps();
        });

         // Insere os dados
         DB::table('materials')->insert([
            ['descricao' => 'Cetim'],
            ['descricao' => 'Dryfit'],
            ['descricao' => 'Eva'],
            ['descricao' => 'Neoprene'],
            ['descricao' => 'Tactel'],
            ['descricao' => 'Atoalhado'],
            ['descricao' => 'Soft'],
            ['descricao' => 'Oxford'],
            ['descricao' => 'Alumínio'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
