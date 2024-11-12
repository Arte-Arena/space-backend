<?php

namespace Database\Seeders;

use App\Models\PedidoStatus;
use Illuminate\Database\Seeder;

class PedidoStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusDesign = [
            'Pendente',
            'Em andamento',
            'Arte OK',
            'Em espera',
            'Cor teste',
            'Análise pendente',
            'Aguardando cliente',
        ];

        for ($i = 0; $i < count($statusDesign); $i++) {
            PedidoStatus::create([
                'nome'  => $statusDesign[$i],
                'fila' => 'D',
            ]);
        }

        $statusProduction = [
            'Pendente',
            'Processando',
            'Renderizando',
            'Impresso',
            'Em Impressão',
            'Separação',
        ];

        for ($i = 0; $i < count($statusProduction); $i++) {
            PedidoStatus::create([
                'nome'  => $statusProduction[$i],
                'fila' => 'I',
            ]);
        }
    }
}
