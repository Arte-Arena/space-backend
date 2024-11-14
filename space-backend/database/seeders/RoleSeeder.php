<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super-admin',
            'ti',
            'admin',
            'lider',
            'colaborador',
            'designer',
            'design-coordinator',
            'production',
            'backoffice',
            'commercial'
        ];

        for ($i = 0; $i < count($roles); $i++) {
            Role::create([
                'name' => $roles[$i],
            ]);
        }
    }
}
