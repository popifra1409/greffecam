<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les rôles de base
        $adminRole = Role::create(['name' => 'Administrateur']);
        $greffierChefRole = Role::create(['name' => 'Greffier en Chef']);
        $greffierRole = Role::create(['name' => 'Greffier']);
        $informaticienRole = Role::create(['name' => 'Informaticien']);

        // Créer un utilisateur admin
        $admin = User::create([
            'name' => 'Administrateur',
            'email' => 'admin@justice.cm',
            'password' => bcrypt('12345678'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $admin->assignRole($adminRole);
    }
}
