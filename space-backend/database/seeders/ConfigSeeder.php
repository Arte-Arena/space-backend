<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'user_id' => '1',
                'custo_tecido' => '4.05',
                'custo_tinta' => '1.75',
                'custo_papel' => '1.90',
                'custo_imposto' => '10',
                'custo_final' => '1524.60',
            ],
        ];

        foreach ($configs as $config) {
            Config::create($config);
        }
    }
}
