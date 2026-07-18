<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MotifMouvement;

class MotifMouvementSeeder extends Seeder
{
    public function run(): void
    {
        $motifs = [
            ['code' => 'loyer', 'libelle' => 'Loyer', 'type_mouvement' => 'versement'],
            ['code' => 'arrieres_loyer', 'libelle' => 'Arriérés de loyer', 'type_mouvement' => 'versement'],
            ['code' => 'avance', 'libelle' => 'Avance', 'type_mouvement' => 'versement'],
            ['code' => 'vente_bien', 'libelle' => 'Vente de bien', 'type_mouvement' => 'versement'],
            ['code' => 'remboursement', 'libelle' => 'Remboursement', 'type_mouvement' => 'retrait'],
            ['code' => 'frais_gestion', 'libelle' => 'Frais de gestion', 'type_mouvement' => 'retrait'],
            ['code' => 'distribution_heritiers', 'libelle' => 'Distribution aux héritiers', 'type_mouvement' => 'retrait'],
            ['code' => 'restitution', 'libelle' => 'Restitution en fin de séquestre', 'type_mouvement' => 'retrait'],
            ['code' => 'autre', 'libelle' => 'Autre', 'type_mouvement' => 'les_deux'],
        ];

        foreach ($motifs as $motif) {
            MotifMouvement::firstOrCreate(['code' => $motif['code']], $motif);
        }
    }
}
