<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Démarrage du seeding...');

        $this->call([
            // 1. Rôles, permissions et utilisateurs de base (ordre critique : en premier)
            RolePermissionSeeder::class,

            // 2. Référentiels indépendants
            TypeDocumentSeeder::class,
            ModuleAccessSeeder::class,

            SectionSeeder::class,
            CategorieDecisionSeeder::class,
            TypeDecisionSeeder::class,
        ]);

        $this->command->info('✅ Seeding terminé avec succès !');
    }
}
