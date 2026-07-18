<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StatutSequestre;

class StatutSequestreSeeder extends Seeder
{
    public function run(): void
    {
        $statuts = [
            ['code' => 'ouvert', 'libelle' => 'Ouvert', 'couleur' => 'success', 'bloque_mouvements' => false, 'ordre' => 1],
            ['code' => 'appel', 'libelle' => 'Appel', 'couleur' => 'warning', 'bloque_mouvements' => false, 'ordre' => 2],
            ['code' => 'suspendu', 'libelle' => 'Suspendu', 'couleur' => 'danger', 'bloque_mouvements' => true, 'ordre' => 3],
            ['code' => 'cloture', 'libelle' => 'Clôturé', 'couleur' => 'gray', 'bloque_mouvements' => true, 'ordre' => 4],
        ];

        foreach ($statuts as $statut) {
            StatutSequestre::firstOrCreate(['code' => $statut['code']], $statut);
        }
    }
}
