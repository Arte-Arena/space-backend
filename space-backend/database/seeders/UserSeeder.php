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
                'name' => 'Gabriel',
                'email' => 'gabriel@artearena.com.br',
                'password' => Hash::make('artearena'),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
