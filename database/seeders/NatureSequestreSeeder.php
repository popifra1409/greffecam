<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NatureSequestre;

class NatureSequestreSeeder extends Seeder
{
    public function run(): void
    {
        $natures = [
            ['code' => 'succession', 'libelle' => 'Succession', 'description' => 'Gestion d\'une succession'],
            ['code' => 'sequestre', 'libelle' => 'Séquestre', 'description' => 'Séquestre judiciaire simple'],
            ['code' => 'administration', 'libelle' => 'Administration', 'description' => 'Administration judiciaire de biens'],
            ['code' => 'tutelle', 'libelle' => 'Tutelle', 'description' => 'Gestion sous tutelle'],
            ['code' => 'curatelle', 'libelle' => 'Curatelle', 'description' => 'Gestion sous curatelle'],
        ];

        foreach ($natures as $nature) {
            NatureSequestre::firstOrCreate(['code' => $nature['code']], $nature);
        }
    }
}
