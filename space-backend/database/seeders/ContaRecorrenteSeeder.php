<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContaRecorrenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('contas_recorrentes')->insert([
            [
                'conta_id' => 1,
                'periodo_recorrencia' => 30,
                'data_proxima_recorrencia' => '2025-01-10',
                'recorrencias_restantes' => 12,
            ],
            [
                'conta_id' => 2,
                'periodo_recorrencia' => 7,
                'data_proxima_recorrencia' => '2024-12-05',
                'recorrencias_restantes' => 4,
            ],
            [
                'conta_id' => 4,
                'periodo_recorrencia' => 30,
                'data_proxima_recorrencia' => '2025-01-26',
                'recorrencias_restantes' => 3,
            ],
        ]);
    }
}
