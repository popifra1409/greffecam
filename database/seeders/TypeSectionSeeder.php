<?php

namespace Database\Seeders;

use App\Models\TypeSection;
use Illuminate\Database\Seeder;

class TypeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'libelle' => 'Civil',
                'code' => 'CIV',
                'description' => 'Section civile',
                'types_parties' => [
                    'demandeur' => 'Demandeur',
                    'defendeur' => 'Défendeur',
                    'temoin' => 'Témoin',
                ],
                'utilise_assesseur' => false,
            ],
            [
                'libelle' => 'Commercial',
                'code' => 'COMM',
                'description' => 'Section commerciale',
                'types_parties' => [
                    'demandeur' => 'Demandeur',
                    'defendeur' => 'Défendeur',
                    'temoin' => 'Témoin',
                ],
                'utilise_assesseur' => false,
            ],
            [
                'libelle' => 'Social',
                'code' => 'SOC',
                'description' => 'Section sociale',
                'types_parties' => [
                    'demandeur' => 'Demandeur',
                    'defendeur' => 'Défendeur',
                    'temoin' => 'Témoin',
                ],
                'utilise_assesseur' => false,
            ],
            [
                'libelle' => 'Correctionnel',
                'code' => 'CORR',
                'description' => 'Section correctionnelle',
                'types_parties' => [
                    'ministere_public' => 'Ministère Public',
                    'partie_civile' => 'Partie Civile',
                    'prevenu' => 'Prévenu',
                    'temoin' => 'Témoin',
                ],
                'utilise_assesseur' => false,
            ],
            [
                'libelle' => 'Tribunal de Droit Local (TDL)',
                'code' => 'TDL',
                'description' => 'Tribunal de Droit Local',
                'types_parties' => [
                    'ministere_public' => 'Ministère Public',
                    'partie_civile' => 'Partie Civile',
                    'prevenu' => 'Prévenu',
                    'temoin' => 'Témoin',
                ],
                'utilise_assesseur' => true,
            ],
        ];

        foreach ($types as $type) {
            TypeSection::create($type);
        }
    }
}
