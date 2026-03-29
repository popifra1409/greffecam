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
                'available' => false, // Pas encore développé
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
}