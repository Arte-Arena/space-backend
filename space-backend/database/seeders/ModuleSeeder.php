<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'contas',
            'vendas',
            'chat',
            'orcamento',
            'producao',
            'reposicao',
        ];

        for ($i = 0; $i < count($modules); $i++) {
            Module::create([
                'name' => $modules[$i],
            ]);
        }
    }
}
