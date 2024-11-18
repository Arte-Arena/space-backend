<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            switch ($user->email) {
                case 'superadmin@artearena.com.br':
                    $user->roles()->attach(Role::where('name', 'super-admin')->first()->id);
                    break;
                case 'admin@artearena.com.br':
                    $user->roles()->attach(Role::where('name', 'admin')->first()->id);
                    break;
                case 'ti@artearena.com.br':
                    $user->roles()->attach(Role::where('name', 'ti')->first()->id);
                    break;
                case 'vendedor@artearena.com.br':
                    $user->roles()->attach(Role::where('name', 'comercial')->first()->id);
                    break;
                case 'designer@artearena.com.br':
                    $user->roles()->attach(Role::where('name', 'designer')->first()->id);
                    break;
                case 'prod@artearena.com.br':
                    $user->roles()->attach(Role::where('name', 'producao')->first()->id);
                    break;
                case 'lider@artearena.com.br':
                    $user->roles()->attach(Role::where('name', 'lider')->first()->id);
                    break;
                default:
            }
        }
    }
}
