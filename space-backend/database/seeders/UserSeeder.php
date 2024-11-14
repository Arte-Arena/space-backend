<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Gabriel Felix',
                'email' => 'gabriel@artearena.com.br',
                'password' => Hash::make('$2y$10$gSiGadoIZicYUfi8Y6rgZ.Uog7ZEyakf.tbfKw.h6rEdwe/.C07tS'),
            ],
            [
                'name' => 'Super Admin Arte Arena',
                'email' => 'superadmin@artearena.com.br',
                'password' => Hash::make('artearena'),
            ],
            [
                'name' => 'Contato Arte Arena',
                'email' => 'contato@artearena.com.br',
                'password' => Hash::make('artearena'),
            ],
            [
                'name' => 'Jessica',
                'email' => 'jessica@artearena.com.br',
                'password' => Hash::make('artearena'),
            ],
            [
                'name' => 'Leandro',
                'email' => 'leandro@artearena.com.br',
                'password' => Hash::make('artearena'),
            ],
            [
                'name' => 'Giovana Gfrasao',
                'email' => 'giovanagfrasao@artearena.com.br',
                'password' => '$2y$10$hqori3hq/sLawR/NaqgXj.cVZmUU9ZCzpClkShDbyCCnpyUppgikaB',
            ],
            [
                'name' => 'Bruno Hortegabruno',
                'email' => 'bruno.hortegabruno@outlook.com',
                'password' => '$2y$10$orgXfZ.MWkn5aVTdfxYn1esvXCzMOv6i2abp7/kVBuiv76A/2pquO',
            ],
            [
                'name' => 'Flavia Agama',
                'email' => 'flaviaagama@artearena.com.br',
                'password' => '$2y$10$gSiGadoIZicYUfi8Y6rgZ.Uog7ZEyakf.tbfKw.h6rEdwe/.C07tS',
            ],
            [
                'name' => 'Ana Carvalho',
                'email' => 'anacarvalho@artearena.com.br',
                'password' => '$2y$10$orgXfZ.MWkn5aVTdfxYn1esvXCzMOv6i2abp7/kVBuiv76A/2pquO',
            ],
            [
                'name' => 'Michel',
                'email' => 'michelmichel@artearena.com.br',
                'password' => '$2y$10$h3KVQ4rpHFrrQJa2Gncs/uQzVQkZK85ugYyrlnPwa/mg4TyrO4vLe',
            ],
            [
                'name' => 'Bruno Carvalho',
                'email' => 'brunocarvalhobp15@gmail.com',
                'password' => '$2y$10$WSG23WMtM1UOcv/4ZrW5YeIgDSaHl83IwRwx3GkBOePtWQcDRd.zC',
            ],
            [
                'name' => 'Altamiro Junior',
                'email' => 'altamiroaltamirojunior5@gmail.com',
                'password' => '$2y$10$QQi72QwYYmym8Bi1ZlKbhub1Ub8hbpWSC/Xswxpou0XLdiuAMEmJ6',
            ],
            [
                'name' => 'William William F. Design',
                'email' => 'williamwilliamfdesign@gmail.com',
                'password' => '$2y$10$.XcxHyI.jc94g9XHA2mwROBGnQ6gdphA8Yop6A70uguHxXVpSO8CO',
            ],
            [
                'name' => 'Lucas Santana',
                'email' => 'lucassantanalucassantanaps323@gmail.com',
                'password' => '$2y$10$BPyup8rBqG5/N9zwFHZKnedWvLB6GkRPXRls0024vC0jhLKML5n9e',
            ],
            [
                'name' => 'Divaed',
                'email' => 'divaed@artearena.com.br',
                'password' => '$2y$10$6vpTjlQJIp9.apqtcFQtduGw75PEZrjWoVIw2XY5J7nuaBJZJ8XQO',
            ],
            [
                'name' => 'Eduardo Léduardo Alexandrino',
                'email' => 'eduardoleduardo.alexandrino@gmail.com',
                'password' => '$2y$10$8wE/CBlRppOqqlCvfxCgzeuofhxMROiRHD/u2d03P77wGlSmW1tSq',
            ],
            [
                'name' => 'Iara Czylberman',
                'email' => 'iaraiczylberman@gmail.com',
                'password' => '$2y$10$ceTZrqG/azycf0ujdg03j.qmMHJM8tdv6HClr4w9yxeXUtHAcTDMS',
            ],
            [
                'name' => 'Marcelo Lopes',
                'email' => 'marcelo.lopesdelima80@gmail.com',
                'password' => '$2y$10$2mSF.Hl2xOnQsYsvhe6Wr.KTmmk4kyc2lFZQ4eD9Kzkfa.q.6rRjW',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
