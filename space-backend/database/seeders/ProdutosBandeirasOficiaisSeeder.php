<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdutosBandeirasOficiaisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $produtos = [
            [
                'nome' => 'Bandeira  - Oficial',
                'preco' => 35.00,
                'prazo' => 5,
                'peso' => 0.15,
                'largura' => 20.00,
                'altura' => 5.00,
                'comprimento' => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Bandeira  - Oficial - 0.14 X 0.20 - Dupla Face',
                'preco' => 70.00,
                'prazo' => 5,
                'peso' => 0.80,
                'largura' => 20.00,
                'altura' => 5.00,
                'comprimento' => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Bandeira  - Oficial - 0.14 X 0.20 - Uma face',
                'preco' => 35.00,
                'prazo' => 5,
                'peso' => 0.04,
                'largura' => 20.00,
                'altura' => 5.00,
                'comprimento' => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // ... continuing with all products
            [
                'nome' => 'Bandeira  - Oficial - 7.20 x 10.28 - Uma face',
                'preco' => 2200.00,
                'prazo' => 5,
                'peso' => 0.15,
                'largura' => 20.00,
                'altura' => 5.00,
                'comprimento' => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Bandeira Oficial - Ed Sheeran - ML',
                'preco' => 70.00,
                'prazo' => 5,
                'peso' => 0.15,
                'largura' => 20.00,
                'altura' => 5.00,
                'comprimento' => 20.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert in chunks to avoid memory issues with large datasets
        foreach (array_chunk($produtos, 100) as $chunk) {
            DB::table('produtos_bandeiras_oficiais')->insert($chunk);
        }
    }
} 