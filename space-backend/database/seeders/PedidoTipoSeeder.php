<?php

namespace Database\Seeders;

use App\Models\PedidoTipo;
use Illuminate\Database\Seeder;

class PedidoTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            'Prazo normal',
            'Antecipação',
            'Faturado',
            'Metade/Metade',
            'Amostra',
        ];

        for ($i = 0; $i < count($tipos); $i++) {
            PedidoTipo::create([
                'nome' => $tipos[$i],
            ]);
        }
    }
}
