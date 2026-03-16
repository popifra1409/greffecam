<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            // Sections Non Répressives
            [
                'libelle' => 'Civil',
                'code' => 'CIV',
                'type' => 'non_repressive',
                'description' => 'Section civile - contentieux entre particuliers',
                'utilise_assesseur' => false,
            ],
            [
                'libelle' => 'Commercial',
                'code' => 'COMM',
                'type' => 'non_repressive',
                'description' => 'Section commerciale - litiges commerciaux',
                'utilise_assesseur' => false,
            ],
            [
                'libelle' => 'Social',
                'code' => 'SOC',
                'type' => 'non_repressive',
                'description' => 'Section sociale - droit du travail',
                'utilise_assesseur' => false,
            ],
            [
                'libelle' => 'Terres et Domaines',
                'code' => 'TDL',
                'type' => 'non_repressive',
                'description' => 'Section terres, domaines et litiges fonciers',
                'utilise_assesseur' => true,
            ],

            // Sections Répressives
            [
                'libelle' => 'Correctionnel',
                'code' => 'CORR',
                'type' => 'repressive',
                'description' => 'Section correctionnelle - délits',
                'utilise_assesseur' => false,
            ],
            [
                'libelle' => 'Simple Police',
                'code' => 'SP',
                'type' => 'repressive',
                'description' => 'Section de simple police - contraventions',
                'utilise_assesseur' => false,
            ],
        ];

        foreach ($sections as $section) {
            Section::firstOrCreate(
                ['code' => $section['code']],
                $section
            );
        }
    }
}
