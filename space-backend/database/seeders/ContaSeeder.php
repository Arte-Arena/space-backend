<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('contas')->insert([
            [
                'user_id' => 2,
                'titulo' => 'Aluguel do Galpão',
                'descricao' => 'Pagamento do aluguel mensal do galpão de produção.',
                'valor' => 5000.00,
                'data_vencimento' => '2024-12-10',
                'status' => 'pendente',
                'tipo' => 'a pagar',
            ],
            [
                'user_id' => 2,
                'titulo' => 'Compra de Tecido',
                'descricao' => 'Compra de tecido para produção de bandeiras.',
                'valor' => 2000.00,
                'data_vencimento' => '2024-11-28',
                'status' => 'pendente',
                'tipo' => 'a pagar',
            ],
            [
                'user_id' => 2,
                'titulo' => 'Venda de Uniformes Escolares',
                'descricao' => 'Recebimento do pagamento de uniformes escolares entregues.',
                'valor' => 3500.00,
                'data_vencimento' => '2024-12-05',
                'status' => 'pendente',
                'tipo' => 'a receber',
            ],
            [
                'user_id' => 2,
                'titulo' => 'Compra de Acaí para evento de lançamento',
                'descricao' => 'Recebimento de convidados servindo açaí.',
                'valor' => 151.84,
                'data_vencimento' => '2024-12-27',
                'status' => 'pendente',
                'tipo' => 'a pagar',
            ],
        ]);
    }
}
