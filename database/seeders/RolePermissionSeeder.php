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
        // CRÉATION DES PERMISSIONS (idempotent, jamais de doublon)
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

            // Référentiels (génériques : tribunaux, sections, matières, types, natures, etc.)
            'view_referentiels',
            'manage_referentiels',

            // ✅ Grades (référentiel dédié Juges/Greffiers)
            'view_grades',
            'manage_grades',

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

            // Séquestres & Caution
            'view_sequestres',
            'create_sequestres',
            'edit_sequestres',
            'delete_sequestres',
            'view_mouvements_sequestre',
            'create_mouvements_sequestre',
            'edit_mouvements_sequestre',
            'delete_mouvements_sequestre',

            // ✅ Sous-entités Séquestre : ayants droit / parties adverses
            'view_sequestre_parties',
            'manage_sequestre_parties',

            // ✅ Documents judiciaires sensibles des sous-dossiers de séquestre
            // (courrier, procédure, contrats, quittances) — permission distincte
            // car ce sont des fichiers stockés sur disque privé.
            'view_sequestre_documents',
            'create_sequestre_documents',
            'delete_sequestre_documents',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ================================================================
        // CRÉATION DES RÔLES
        // ✅ givePermissionTo() au lieu de syncPermissions() partout :
        //    on AJOUTE les permissions attendues sans jamais retirer une
        //    permission déjà accordée manuellement via l'interface Filament
        //    entre deux exécutions du seeder.
        // ================================================================

        // 1. SUPER ADMIN (rôle unique dans toute l'application)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrateur']);
        // Le Super Admin doit toujours avoir TOUTES les permissions existantes,
        // y compris celles ajoutées après coup par d'autres modules/seeders.
        $superAdmin->givePermissionTo(Permission::all());

        // 2. ADMINISTRATEUR (plusieurs comptes possibles)
        $admin = Role::firstOrCreate(['name' => 'Administrateur']);
        $admin->givePermissionTo([
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
            'view_grades',
            'manage_grades',
            'view_users',
            'create_users',
            'edit_users',
            'view_roles',
            'manage_roles',
            'view_permissions',
            'manage_permissions',
            'access_decision_recours',
            'access_administration',
            'view_sequestres',
            'create_sequestres',
            'edit_sequestres',
            'delete_sequestres',
            'view_mouvements_sequestre',
            'create_mouvements_sequestre',
            'edit_mouvements_sequestre',
            'delete_mouvements_sequestre',
            'view_sequestre_parties',
            'manage_sequestre_parties',
            'view_sequestre_documents',
            'create_sequestre_documents',
            'delete_sequestre_documents',
        ]);

        // 3. GREFFIER EN CHEF
        $greffierChef = Role::firstOrCreate(['name' => 'Greffier en Chef']);
        $greffierChef->givePermissionTo([
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
            'view_grades',
            'access_decision_recours',
        ]);

        // 4. GREFFIER
        $greffier = Role::firstOrCreate(['name' => 'Greffier']);
        $greffier->givePermissionTo([
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
            'view_grades',
            'access_decision_recours',
        ]);

        // 5. JUGE
        $juge = Role::firstOrCreate(['name' => 'Juge']);
        $juge->givePermissionTo([
            'view_dossiers',
            'view_decisions',
            'sign_decisions',
            'view_recours',
            'view_transmissions',
            'receive_transmissions',
            'view_alertes',
            'view_referentiels',
            'view_grades',
            'access_decision_recours',
        ]);

        // 6. CONSULTANT (lecture seule)
        $consultant = Role::firstOrCreate(['name' => 'Consultant']);
        $consultant->givePermissionTo([
            'view_dossiers',
            'view_decisions',
            'view_recours',
            'view_referentiels',
            'view_grades',
            'access_decision_recours',
        ]);

        // 7. INFORMATICIEN (support technique)
        $informaticien = Role::firstOrCreate(['name' => 'Informaticien']);
        $informaticien->givePermissionTo([
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
            'view_grades',
            'manage_grades',
            'access_decision_recours',
            'access_administration',
        ]);

        // COMPTABLE SÉQUESTRE
        $comptableSequestre = Role::firstOrCreate(['name' => 'Comptable Séquestre']);
        $comptableSequestre->givePermissionTo([
            'view_sequestres',
            'create_sequestres',
            'edit_sequestres',
            'view_mouvements_sequestre',
            'create_mouvements_sequestre',
            'edit_mouvements_sequestre',
            'view_sequestre_parties',
            'manage_sequestre_parties',
            'view_sequestre_documents',
            'create_sequestre_documents',
            'view_referentiels',
            'view_grades',
            'access_sequestre_caution',
        ]);

        // ================================================================
        // CRÉATION DES UTILISATEURS PAR DÉFAUT
        // ✅ firstOrCreate : ne recrée jamais un compte existant, ne réinitialise
        //    jamais le mot de passe d'un compte déjà créé.
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
        // syncRoles ici est sans danger : il ne fait que garantir que CE compte
        // a bien le rôle Super Administrateur, sans toucher aux permissions du rôle.
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
        // Le Super Admin (et l'Administrateur) reçoit l'accès à TOUS les
        // modules déjà enregistrés en base, + une liste de secours couvrant
        // les modules prévus mais pas encore utilisés. Les autres rôles
        // métier gardent un accès limité au module Décision & Recours.
        // ================================================================

        $codesModulesConnus = collect([
            'decision_recours',
            'sequestre_caution',
            'documents_judiciaires',
        ])
            ->merge(ModuleAccess::query()->distinct()->pluck('module_code'))
            ->unique()
            ->values();

        // Super Admin et Administrateur : accès à TOUS les modules, présents et futurs
        foreach ([$superAdmin, $admin] as $role) {
            foreach ($codesModulesConnus as $moduleCode) {
                ModuleAccess::updateOrCreate(
                    ['role_id' => $role->id, 'module_code' => $moduleCode],
                    ['can_access' => true]
                );
            }
        }

        // Autres rôles métier : accès au module Décision & Recours uniquement
        foreach ([$greffierChef, $greffier, $juge, $consultant, $informaticien] as $role) {
            ModuleAccess::updateOrCreate(
                ['role_id' => $role->id, 'module_code' => 'decision_recours'],
                ['can_access' => true]
            );
        }

        ModuleAccess::updateOrCreate(
            ['role_id' => $comptableSequestre->id, 'module_code' => 'sequestre_caution'],
            ['can_access' => true]
        );

        $this->command->info('✅ Rôles, permissions et utilisateurs synchronisés avec succès !');
        $this->command->info('   (mode additif : aucune permission existante n\'a été écrasée)');
        $this->command->info('📧 Super Admin : superadmin@justice.cm / Sadmin@1977');
        $this->command->info('📧 Admin       : admin@justice.cm / 12345678');
    }
}
