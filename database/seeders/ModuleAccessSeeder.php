<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ModuleAccess;
use Spatie\Permission\Models\Role;

class ModuleAccessSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les rôles
        $adminRole = Role::where('name', 'Administrateur')->first();
        $greffierRole = Role::where('name', 'Greffier')->first();

        if ($adminRole) {
            // Admin a accès à tout
            ModuleAccess::updateOrCreate(
                ['role_id' => $adminRole->id, 'module_code' => 'decision_recours'],
                ['can_access' => true]
            );
        }

        if ($greffierRole) {
            // Greffier a accès au module decision_recours
            ModuleAccess::updateOrCreate(
                ['role_id' => $greffierRole->id, 'module_code' => 'decision_recours'],
                ['can_access' => true]
            );
        }
    }
}