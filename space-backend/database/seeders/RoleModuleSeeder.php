<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Module;

class RoleModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super-admin')->first();
        $comercialRole = Role::where('name', 'comercial')->first();
        $designerRole = Role::where('name', 'designer')->first();

        $allModules = Module::all();
        $vendasModule = Module::where('name', 'vendas')->first();
        $chatModule = Module::where('name', 'chat')->first();
        $orcamentoModule = Module::where('name', 'orcamento')->first();
        $producaoModule = Module::where('name', 'producao')->first();

        // Assign all modules to super-admin role
        foreach ($allModules as $module) {
            $superAdminRole->modules()->attach($module->id);
        }

        // Assign specific modules to comercial role
        $comercialRole->modules()->attach([$vendasModule->id, $chatModule->id, $orcamentoModule->id, $producaoModule->id]);

        // Assign specific modules to designer role
        $designerRole->modules()->attach([$producaoModule->id]);
    }
}
