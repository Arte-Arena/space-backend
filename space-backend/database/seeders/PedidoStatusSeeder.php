<?php

namespace Database\Seeders;

use App\Models\PedidoStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PedidoStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Desativa as verificações de chave estrangeira
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Trunca a tabela
        DB::table('pedidos_status')->truncate();

        // Reativa as verificações de chave estrangeira
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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

        $statusConfeccao = [ 
            'Pendente',
            'Costurado',
        ];

        for ($i = 0; $i < count($statusConfeccao); $i++) {
            PedidoStatus::create([
                'nome'  => $statusConfeccao[$i],
                'fila' => 'C',
            ]);
        }

        $statusSulimacao = [ 
            'Pendente',
            'Calandra',
            'Prensa',
        ];

        for ($i = 0; $i < count($statusSulimacao); $i++) {
            PedidoStatus::create([
                'nome'  => $statusSulimacao[$i],
                'fila' => 'S',
            ]);
        }

        $statusCostura = [ 
            'Pendente',
            'Calandra',
            'Prensa',
        ];

        for ($i = 0; $i < count($statusCostura); $i++) {
            PedidoStatus::create([
                'nome'  => $statusCostura[$i],
                'fila' => 'F',
            ]);
        }

        $statusCorteConferencia = [ 
            'Pendente',
            'Calandra',
            'Prensa',
        ];

        for ($i = 0; $i < count($statusCorteConferencia); $i++) {
            PedidoStatus::create([
                'nome'  => $statusCorteConferencia[$i],
                'fila' => 'R',
            ]);
        }

        $statusEntrega = [ 
            'Em Separação',
            'Retirada',
            'Em Entrega',
            'Entregue',
            'Devolução',
        ];

        for ($i = 0; $i < count($statusEntrega); $i++) {
            PedidoStatus::create([
                'nome'  => $statusEntrega[$i],
                'fila' => 'E',
            ]);
        }
    }
}
