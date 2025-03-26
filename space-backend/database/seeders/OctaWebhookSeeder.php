<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OctaWebhookSeeder extends Seeder
{
    public function run(): void
    {
        $octa_webhook = [
            [
                'nome' => 'João Silva',
                'telefone' => '123456789',
                'email' => 'joao.silva@example.com',
                'origem' => 'Website',
                'url_octa' => 'https://example.com',
                'id' => 1,
                'primeira_mensagem_cliente' => 'Olá, estou interessado no seu produto.',
                'responsavel_contato' => 'Maria Oliveira',
                'tel_comercial_contato' => '987654321',
                'tel_residencial_contato' => '123123123',
                'status_do_contato' => 'Em andamento',
                'numero_de_pedido_contato' => '12345',
                'nome_organizacao' => 'Organização Exemplo',
                'primeiro_telefone_organizacao' => '321321321',
                'primeiro_dominio_organizacao' => 'organizacaoexemplo.com',
                'empresa' => 'Empresa Exemplo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Pedro Costa',
                'telefone' => '555555555',
                'email' => 'pedro.costa@example.com',
                'origem' => 'Site',
                'url_octa' => 'https://example.net',
                'id' => 2,
                'primeira_mensagem_cliente' => 'Olá, estou interessado em sua solução.',
                'responsavel_contato' => 'Ana Maria',
                'tel_comercial_contato' => '5555555555',
                'tel_residencial_contato' => '555555555',
                'status_do_contato' => 'Finalizado',
                'numero_de_pedido_contato' => '67890',
                'nome_organizacao' => 'Organização Fictícia',
                'primeiro_telefone_organizacao' => '555111222',
                'primeiro_dominio_organizacao' => 'organizaoficticia.com',
                'empresa' => 'Empresa Fictícia',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('octa_webhook')->insert($octa_webhook);

    }
}
