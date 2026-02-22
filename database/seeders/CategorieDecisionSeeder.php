<?php

namespace Database\Seeders;

use App\Models\CategorieDecision;
use Illuminate\Database\Seeder;

class CategorieDecisionSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'libelle' => 'Jugement',
                'code' => 'JUG',
                'description' => 'Décision rendue par un tribunal de première instance, Exemple: ADD, AU FOND, CHAMBRE de CONSEIL',
            ],
            [
                'libelle' => 'Ordonnance',
                'code' => 'ORD',
                'description' => 'Décision rendue par un juge unique, Exemple: Reféré ordinaire, référé d\'heures à heures',
            ],
            [
                'libelle' => 'Procès-verbeaux',
                'code' => 'PV',
                'description' => 'Exemple: prestations de serment, conciliation',
            ],
            [
                'libelle' => 'Arrêt',
                'code' => 'ARR',
                'description' => 'Décision rendue par une juridiction d\'appel',
            ],
            [
                'libelle' => 'Sentence',
                'code' => 'SEN',
                'description' => 'Décision arbitrale',
            ],
        ];

        foreach ($categories as $categorie) {
            CategorieDecision::create($categorie);
        }
    }
}