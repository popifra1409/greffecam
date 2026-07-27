<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NatureSequestre;

class NatureSequestreSeeder extends Seeder
{
    public function run(): void
    {
        $natures = [
            [
                'code' => 'succession',
                'libelle' => 'Succession',
                'description' => 'Gestion d\'une succession',
                'libelle_ayants_droit' => 'Héritiers',
                'libelle_parties_adverses' => 'Débiteurs de la succession',
            ],
            [
                'code' => 'sequestre',
                'libelle' => 'Séquestre',
                'description' => 'Séquestre judiciaire simple (ex: loyers)',
                'libelle_ayants_droit' => 'Ayants droit',
                'libelle_parties_adverses' => 'Locataires (versants)',
            ],
            [
                'code' => 'administration',
                'libelle' => 'Administration',
                'description' => 'Administration judiciaire de biens',
                'libelle_ayants_droit' => 'Bénéficiaires',
                'libelle_parties_adverses' => 'Contributeurs',
            ],
            [
                'code' => 'tutelle',
                'libelle' => 'Tutelle',
                'description' => 'Gestion sous tutelle',
                'libelle_ayants_droit' => 'Ayants droit',
                'libelle_parties_adverses' => 'Débirentiers',
            ],
            [
                'code' => 'curatelle',
                'libelle' => 'Curatelle',
                'description' => 'Gestion sous curatelle',
                'libelle_ayants_droit' => 'Bénéficiaire',
                'libelle_parties_adverses' => 'Débirentiers',
            ],
        ];

        foreach ($natures as $nature) {
            $enregistrement = NatureSequestre::firstOrCreate(
                ['code' => $nature['code']],
                $nature
            );

            // ✅ Si le libellé personnalisé n'a jamais été renseigné (ni par ce
            // seeder, ni manuellement), on le complète — sans jamais écraser
            // une personnalisation déjà faite par l'utilisateur via l'interface.
            $mettreAJour = [];

            if (empty($enregistrement->libelle_ayants_droit)) {
                $mettreAJour['libelle_ayants_droit'] = $nature['libelle_ayants_droit'];
            }

            if (empty($enregistrement->libelle_parties_adverses)) {
                $mettreAJour['libelle_parties_adverses'] = $nature['libelle_parties_adverses'];
            }

            if (!empty($mettreAJour)) {
                $enregistrement->update($mettreAJour);
            }
        }
    }
}
