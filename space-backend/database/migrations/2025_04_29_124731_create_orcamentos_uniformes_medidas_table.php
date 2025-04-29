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
        Schema::create('orcamentos_uniformes_medidas', function (Blueprint $table) {
            $table->id();
            $table->string('genero');
            $table->string('tamanho_camisa');
            $table->string('tamanho_calcao');
            $table->integer('largura_camisa');
            $table->integer('altura_camisa');
            $table->integer('largura_calcao');
            $table->integer('altura_calcao');
            $table->timestamps();
        });

        $this->inserirMedidasMasculinas();

        $this->inserirMedidasFemininas();

        $this->inserirMedidasInfantis();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orcamentos_uniformes_medidas');
    }

    private function inserirMedidasMasculinas(): void
    {
        $medidas = [
            ['PP', 48, 62, 60, 44],
            ['P', 51, 66, 66, 50],
            ['M', 55, 71, 68, 52],
            ['G', 58, 76, 70, 54],
            ['GG', 60, 78, 72, 56],
            ['XG', 61, 80, 74, 58],
            ['XXG', 64, 83, 78, 61],
            ['XXXG', 68, 83, 82, 64]
        ];

        foreach ($medidas as $medida) {
            DB::table('orcamentos_uniformes_medidas')->insert([
                'genero' => 'MASCULINO',
                'tamanho_camisa' => $medida[0],
                'tamanho_calcao' => $medida[0],
                'largura_camisa' => $medida[1],
                'altura_camisa' => $medida[2],
                'largura_calcao' => $medida[3],
                'altura_calcao' => $medida[4],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function inserirMedidasFemininas(): void
    {
        $medidas = [
            ['PP', 47, 54, 34, 58],
            ['P', 50, 58, 36, 60],
            ['M', 52, 60, 40, 65],
            ['G', 54, 63, 44, 71],
            ['GG', 56, 65, 47, 77],
            ['XG', 59, 68, 52, 84],
            ['XXG', 61, 72, 56, 91],
            ['XXXG', 64, 76, 56, 95]
        ];

        foreach ($medidas as $medida) {
            DB::table('orcamentos_uniformes_medidas')->insert([
                'genero' => 'FEMININO',
                'tamanho_camisa' => $medida[0],
                'tamanho_calcao' => $medida[0],
                'largura_camisa' => $medida[1],
                'altura_camisa' => $medida[2],
                'largura_calcao' => $medida[3],
                'altura_calcao' => $medida[4],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function inserirMedidasInfantis(): void
    {
        $medidas = [
            ['2', 28, 37, 37, 27],
            ['4', 30, 42, 39, 29],
            ['6', 33, 45, 42, 31],
            ['8', 35, 48, 44, 33],
            ['10', 37, 50, 47, 35],
            ['12', 39, 53, 49, 37],
            ['14', 40, 58, 52, 39]
        ];

        foreach ($medidas as $medida) {
            DB::table('orcamentos_uniformes_medidas')->insert([
                'genero' => 'INFANTIL',
                'tamanho_camisa' => $medida[0],
                'tamanho_calcao' => $medida[0],
                'largura_camisa' => $medida[1],
                'altura_camisa' => $medida[2],
                'largura_calcao' => $medida[3],
                'altura_calcao' => $medida[4],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
};
