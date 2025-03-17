<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdutosPersonalizadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $produtos = [
            [
                'nome' => 'Abadá Personalizado',
                'preco' => 45.00,
                'prazo' => 7,
                'peso' => 0.20,
                'largura' => 30.00,
                'altura' => 2.00,
                'comprimento' => 40.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Camiseta Personalizada - Malha Premium',
                'preco' => 55.00,
                'prazo' => 5,
                'peso' => 0.25,
                'largura' => 30.00,
                'altura' => 2.00,
                'comprimento' => 40.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Caneca Personalizada - Cerâmica',
                'preco' => 35.00,
                'prazo' => 4,
                'peso' => 0.35,
                'largura' => 12.00,
                'altura' => 10.00,
                'comprimento' => 12.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Banner Personalizado - Lona 440g',
                'preco' => 75.00,
                'prazo' => 3,
                'peso' => 0.50,
                'largura' => 100.00,
                'altura' => 2.00,
                'comprimento' => 70.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Adesivo Personalizado - Vinil',
                'preco' => 25.00,
                'prazo' => 2,
                'peso' => 0.10,
                'largura' => 30.00,
                'altura' => 1.00,
                'comprimento' => 30.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('produtos_personalizad')->insert($produtos);
    }
} 