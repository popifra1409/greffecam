<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class ModuleAccess extends Model
{
    protected $fillable = [
        'role_id',
        'module_code',
        'can_access',
    ];

    protected $casts = [
        'can_access' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Liste des modules disponibles
    public static function getAvailableModules(): array
    {
        return [
            'decision_recours' => [
                'name' => 'Décision & Recours',
                'description' => 'Gestion des dossiers, décisions judiciaires, recours et transmissions',
                'icon' => 'heroicon-o-scale',
                'color' => 'amber',
                'url' => '/decision-recours',
                'features' => [
                    'Enrôlement des dossiers',
                    'Rédaction et suivi des décisions',
                    'Gestion des voies de recours',
                    'Transmissions entre services',
                    'Alertes de délais',
                ],
            ],
            'sequestre_caution' => [
                'name' => 'Séquestre & Caution',
                'description' => 'Gestion des séquestres judiciaires et des cautions',
                'icon' => 'heroicon-o-lock-closed',
                'color' => 'green',
                'url' => '/sequestre-caution',
                'features' => [
                    'Enregistrement des séquestres',
                    'Gestion des cautions',
                    'Suivi des montants',
                    'Historique des mouvements',
                ],
                'available' => true,
            ],
            'documents_judiciaires' => [
                'name' => 'Documents Judiciaires',
                'description' => 'Délivrance de documents : casiers, RCCM, certificats de nationalité',
                'icon' => 'heroicon-o-document-text',
                'color' => 'blue',
                'url' => '/documents-judiciaires',
                'features' => [
                    'Extraits de casier judiciaire',
                    'Certificats RCCM',
                    'Certificats de nationalité',
                    'Notes administratives',
                ],
                'available' => false,
            ],
        ];
    }

    public function getModules(): array
    {
        $user = auth()->user();
        $allModules = ModuleAccess::getAvailableModules();

        // ✅ Super Admin et Administrateur voient tout
        if ($user->hasRole(['Super Administrateur', 'Administrateur'])) {
            return $allModules;
        }

        // Filtrer selon les accès
        $accessibleModules = [];
        foreach ($user->roles as $role) {
            $accesses = ModuleAccess::where('role_id', $role->id)
                ->where('can_access', true)
                ->get();

            foreach ($accesses as $access) {
                if (isset($allModules[$access->module_code])) {
                    $accessibleModules[$access->module_code] = $allModules[$access->module_code];
                }
            }
        }

        return $accessibleModules;
    }
}
