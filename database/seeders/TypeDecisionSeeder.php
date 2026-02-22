<?php

namespace Database\Seeders;

use App\Models\TypeDecision;
use App\Models\CategorieDecision;
use Illuminate\Database\Seeder;

class TypeDecisionSeeder extends Seeder
{
    public function run(): void
    {
        $jugement = CategorieDecision::where('code', 'JUG')->first();
        $ordonnance = CategorieDecision::where('code', 'ORD')->first();
        $pv = CategorieDecision::where('code', 'PV')->first();

        $types = [
            // Jugements
            ['categorie_decision_id' => $jugement->id, 'libelle' => 'Avant-dire Droit', 'code' => 'JUG_AADD'],
            ['categorie_decision_id' => $jugement->id, 'libelle' => 'Au Fond', 'code' => 'JUG_AF'],
            ['categorie_decision_id' => $jugement->id, 'libelle' => 'Chambre de Conseil', 'code' => 'JUG_CC'],
            ['categorie_decision_id' => $jugement->id, 'libelle' => 'Défaut', 'code' => 'JUG_DEF'],
            ['categorie_decision_id' => $jugement->id, 'libelle' => 'Condamnation', 'code' => 'JUG_COND'],
            ['categorie_decision_id' => $jugement->id, 'libelle' => 'Acquittement', 'code' => 'JUG_ACQ'],

            // Ordonnances
            ['categorie_decision_id' => $ordonnance->id, 'libelle' => 'Référé ordinaire', 'code' => 'ORD_RO'],
            ['categorie_decision_id' => $ordonnance->id, 'libelle' => 'Référé d\'heures à heures', 'code' => 'ORD_REF'],
            ['categorie_decision_id' => $ordonnance->id, 'libelle' => 'Contentieux', 'code' => 'ORD_CT'],
            ['categorie_decision_id' => $ordonnance->id, 'libelle' => 'Ordonnances sur requête du PT', 'code' => 'ORD_OPT'],

            // Procès-verbeaux
            ['categorie_decision_id' => $pv->id, 'libelle' => 'Prestations de serment', 'code' => 'PV_PS'],
            ['categorie_decision_id' => $pv->id, 'libelle' => 'Conciliation', 'code' => 'PV_CON'],
        ];

        foreach ($types as $type) {
            TypeDecision::create($type);
        }
    }
}
