<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\ModuleAccess;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Réinitialiser les caches
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ================================================================
        // CRÉATION DES PERMISSIONS
        // ================================================================

        $permissions = [
            // Dossiers
            'view_dossiers',
            'create_dossiers',
            'edit_dossiers',
            'delete_dossiers',
            'export_dossiers',

            // Décisions
            'view_decisions',
            'create_decisions',
            'edit_decisions',
            'delete_decisions',
            'validate_decisions',
            'sign_decisions',
            'export_decisions',

            // Recours
            'view_recours',
            'create_recours',
            'edit_recours',
            'delete_recours',

            // Transmissions
            'view_transmissions',
            'create_transmissions',
            'edit_transmissions',
            'delete_transmissions',
            'receive_transmissions',

            // Alertes
            'view_alertes',
            'manage_alertes',

            // Référentiels
            'view_referentiels',
            'manage_referentiels',

            // Administration
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_roles',
            'manage_roles',
            'view_permissions',
            'manage_permissions',
            'view_audit_logs',

            // Système / Technique
            'manage_system_settings',
            'view_system_logs',
            'manage_backups',

            // Modules
            'access_decision_recours',
            'access_sequestre_caution',
            'access_documents_judiciaires',
            'access_administration',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ================================================================
        // CRÉATION DES RÔLES
        // ================================================================

        // 1. SUPER ADMIN (un seul)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrateur']);
        $superAdmin->syncPermissions(Permission::all()); // Toutes les permissions

        // 2. ADMINISTRATEUR (plusieurs possibles)
        $admin = Role::firstOrCreate(['name' => 'Administrateur']);
        $admin->syncPermissions([
            'view_dossiers',
            'create_dossiers',
            'edit_dossiers',
            'delete_dossiers',
            'export_dossiers',
            'view_decisions',
            'create_decisions',
            'edit_decisions',
            'delete_decisions',
            'validate_decisions',
            'sign_decisions',
            'export_decisions',
            'view_recours',
            'create_recours',
            'edit_recours',
            'delete_recours',
            'view_transmissions',
            'create_transmissions',
            'edit_transmissions',
            'receive_transmissions',
            'view_alertes',
            'manage_alertes',
            'view_referentiels',
            'manage_referentiels',
            'view_users',
            'create_users',
            'edit_users',
            'view_roles',
            'view_permissions',
            'access_decision_recours',
            'access_administration',
        ]);

        // 3. GREFFIER EN CHEF
        $greffierChef = Role::firstOrCreate(['name' => 'Greffier en Chef']);
        $greffierChef->syncPermissions([
            'view_dossiers',
            'create_dossiers',
            'edit_dossiers',
            'export_dossiers',
            'view_decisions',
            'create_decisions',
            'edit_decisions',
            'validate_decisions',
            'export_decisions',
            'view_recours',
            'create_recours',
            'edit_recours',
            'view_transmissions',
            'create_transmissions',
            'edit_transmissions',
            'receive_transmissions',
            'view_alertes',
            'manage_alertes',
            'view_referentiels',
            'access_decision_recours',
        ]);

        // 4. GREFFIER
        $greffier = Role::firstOrCreate(['name' => 'Greffier']);
        $greffier->syncPermissions([
            'view_dossiers',
            'create_dossiers',
            'edit_dossiers',
            'view_decisions',
            'create_decisions',
            'edit_decisions',
            'view_recours',
            'create_recours',
            'edit_recours',
            'view_transmissions',
            'create_transmissions',
            'receive_transmissions',
            'view_alertes',
            'view_referentiels',
            'access_decision_recours',
        ]);

        // 5. JUGE
        $juge = Role::firstOrCreate(['name' => 'Juge']);
        $juge->syncPermissions([
            'view_dossiers',
            'view_decisions',
            'sign_decisions',
            'view_recours',
            'view_transmissions',
            'receive_transmissions',
            'view_alertes',
            'view_referentiels',
            'access_decision_recours',
        ]);

        // 6. CONSULTANT (lecture seule)
        $consultant = Role::firstOrCreate(['name' => 'Consultant']);
        $consultant->syncPermissions([
            'view_dossiers',
            'view_decisions',
            'view_recours',
            'view_referentiels',
            'access_decision_recours',
        ]);

        // 7. INFORMATICIEN (support technique)
        $informaticien = Role::firstOrCreate(['name' => 'Informaticien']);
        $informaticien->syncPermissions([
            'view_users',
            'create_users',
            'edit_users',
            'view_roles',
            'view_permissions',
            'view_audit_logs',
            'manage_system_settings',
            'view_system_logs',
            'manage_backups',
            'view_referentiels',
            'manage_referentiels',
            'access_decision_recours',
            'access_administration',
        ]);

        // ================================================================
        // CRÉATION DES UTILISATEURS PAR DÉFAUT
        // ================================================================

        // Super Admin (accès total)
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@justice.cm'],
            [
                'name' => 'Super Administrateur',
                'password' => bcrypt('Sadmin@1977'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $superAdminUser->syncRoles([$superAdmin]);

        // Administrateur (gestion courante)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@justice.cm'],
            [
                'name' => 'Administrateur',
                'password' => bcrypt('12345678'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $adminUser->syncRoles([$admin]);

        // ================================================================
        // ACCÈS AUX MODULES
        // ================================================================

        $tousLesRoles = [
            $superAdmin,
            $admin,
            $greffierChef,
            $greffier,
            $juge,
            $consultant,
            $informaticien,
        ];

        foreach ($tousLesRoles as $role) {
            ModuleAccess::updateOrCreate(
                ['role_id' => $role->id, 'module_code' => 'decision_recours'],
                ['can_access' => true]
            );
        }

        $this->command->info('✅ Rôles, permissions et utilisateurs créés avec succès !');
        $this->command->info('📧 Super Admin : superadmin@justice.cm / Sadmin@1977');
        $this->command->info('📧 Admin       : admin@justice.cm / 12345678');
    }
}
