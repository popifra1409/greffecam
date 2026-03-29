<?php

/**
 * Script de migration vers l'architecture modulaire
 * Usage: php migrate-to-modules.php
 */

class ModuleMigration
{
    private $dryRun = true; 

    private $modulesMapping = [
        'DecisionRecours' => [
            'resources' => [
                'DossierResource',
                'DecisionResource',
                'RecoursResource',
                'AlerteRecoursResource',
                'TransmissionDecisionResource',
            ],
        ],
        'Portal' => [
            'resources' => [
                'UserResource',
                'RoleResource',
                'PermissionResource',
                // Référentiels - on les laisse dans Portal pour partage entre modules
                'TribunalResource',
                'SectionResource',
                'MatiereResource',
                'InfractionResource',
                'NatureDecisionResource',
                'TypeDecisionResource',
                'CategorieDecisionResource',
                'JugeResource',
                'CollegeJugeResource',
                'GreffierResource',
                'AnneeJudiciaireResource',
                'JourFerieResource',
                'TypeRecoursResource',
            ],
        ],
    ];

    public function run()
    {
        echo "🚀 MIGRATION VERS ARCHITECTURE MODULAIRE\n";
        echo str_repeat("=", 50) . "\n\n";

        if ($this->dryRun) {
            echo "⚠️  MODE DRY RUN - Aucun fichier ne sera déplacé\n\n";
        }

        foreach ($this->modulesMapping as $moduleName => $config) {
            echo "📦 Module: {$moduleName}\n";
            echo str_repeat("-", 50) . "\n";

            foreach ($config['resources'] as $resourceName) {
                $this->migrateResource($resourceName, $moduleName);
            }

            echo "\n";
        }

        echo "✅ Migration terminée!\n\n";
        $this->printNextSteps();
    }

    private function migrateResource(string $resourceName, string $moduleName)
    {
        $oldPath = "app/Filament/Resources/{$resourceName}.php";
        $newPath = "app/Modules/{$moduleName}/Filament/Resources/{$resourceName}.php";

        if (!file_exists($oldPath)) {
            echo "  ❌ {$resourceName}.php n'existe pas\n";
            return;
        }

        // Créer le dossier si nécessaire
        $newDir = dirname($newPath);
        if (!$this->dryRun && !is_dir($newDir)) {
            mkdir($newDir, 0755, true);
        }

        // Lire le contenu
        $content = file_get_contents($oldPath);

        // Remplacer le namespace
        $content = str_replace(
            'namespace App\Filament\Resources;',
            "namespace App\\Modules\\{$moduleName}\\Filament\\Resources;",
            $content
        );

        if (!$this->dryRun) {
            file_put_contents($newPath, $content);
        }

        echo "  ✅ Migré: {$resourceName}.php\n";

        // Migrer les sous-dossiers (Pages, RelationManagers)
        $this->migrateResourceSubfolders($resourceName, $moduleName);
    }

    private function migrateResourceSubfolders(string $resourceName, string $moduleName)
    {
        $resourceFolder = str_replace('Resource', '', $resourceName);
        $oldDir = "app/Filament/Resources/{$resourceFolder}";
        $newDir = "app/Modules/{$moduleName}/Filament/Resources/{$resourceFolder}";

        if (!is_dir($oldDir)) {
            return;
        }

        // Créer le dossier de destination
        if (!$this->dryRun && !is_dir($newDir)) {
            mkdir($newDir, 0755, true);
        }

        // Copier récursivement
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($oldDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $subPath = str_replace($oldDir, '', $item->getPathname());
            $destPath = $newDir . $subPath;

            if ($item->isDir()) {
                if (!$this->dryRun && !is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                // Lire et modifier le namespace
                $content = file_get_contents($item->getPathname());

                // Remplacer les namespaces
                $content = str_replace(
                    'namespace App\Filament\Resources\\' . $resourceFolder,
                    "namespace App\\Modules\\{$moduleName}\\Filament\\Resources\\{$resourceFolder}",
                    $content
                );

                $content = str_replace(
                    'use App\Filament\Resources\\' . $resourceName . ';',
                    "use App\\Modules\\{$moduleName}\\Filament\\Resources\\{$resourceName};",
                    $content
                );

                if (!$this->dryRun) {
                    file_put_contents($destPath, $content);
                }

                echo "    ↳ Migré: {$subPath}\n";
            }
        }
    }

    private function printNextSteps()
    {
        echo "⚠️  PROCHAINES ÉTAPES MANUELLES:\n";
        echo str_repeat("-", 50) . "\n";
        echo "1. Créer les panels Filament:\n";
        echo "   php artisan make:filament-panel decision-recours\n";
        echo "   php artisan make:filament-panel portal\n\n";

        echo "2. Vérifier les fichiers migrés:\n";
        echo "   - Namespaces corrects\n";
        echo "   - Imports 'use' corrects\n\n";

        echo "3. Mettre à jour les PanelProviders\n\n";

        echo "4. Tester l'application:\n";
        echo "   php artisan serve\n\n";

        echo "5. Si tout fonctionne, supprimer les anciens fichiers:\n";
        echo "   rm -rf app/Filament/Resources/Dossier*\n";
        echo "   rm -rf app/Filament/Resources/Decision*\n";
        echo "   rm -rf app/Filament/Resources/Recours*\n";
        echo "   rm -rf app/Filament/Resources/Alerte*\n";
        echo "   rm -rf app/Filament/Resources/Transmission*\n\n";
    }
}

// Exécution
$migration = new ModuleMigration();
$migration->run();