<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeDocument;

class TypeDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $typeDocuments = [
            [
                'code' => 'pv_reception',
                'libelle' => 'PV de réception',
                'icone' => '📋',
                'description' => 'Procès-verbal de réception du recours au greffe',
            ],
            [
                'code' => 'pv_notification_appelant',
                'libelle' => 'PV de notification à l\'appelant',
                'icone' => '📬',
                'description' => 'Procès-verbal de notification du recours à l\'appelant',
            ],
            [
                'code' => 'pv_notification_intime',
                'libelle' => 'PV de notification à l\'intimé',
                'icone' => '📬',
                'description' => 'Procès-verbal de notification du recours à l\'intimé',
            ],
            [
                'code' => 'memoire_appelant',
                'libelle' => 'Mémoire de l\'appelant',
                'icone' => '📝',
                'description' => 'Mémoire en appel déposé par l\'appelant',
            ],
            [
                'code' => 'memoire_intime',
                'libelle' => 'Mémoire de l\'intimé',
                'icone' => '📝',
                'description' => 'Mémoire en défense déposé par l\'intimé',
            ],
            [
                'code' => 'pieces_justificatives',
                'libelle' => 'Pièces justificatives',
                'icone' => '📎',
                'description' => 'Documents et pièces annexes',
            ],
            [
                'code' => 'ordonnance_cloture',
                'libelle' => 'Ordonnance de clôture',
                'icone' => '⚖️',
                'description' => 'Ordonnance de clôture de l\'instruction',
            ],
            [
                'code' => 'bordereau_pieces',
                'libelle' => 'Bordereau de pièces',
                'icone' => '📑',
                'description' => 'Bordereau récapitulatif des pièces',
            ],
            [
                'code' => 'autre',
                'libelle' => 'Autre document',
                'icone' => '📄',
                'description' => 'Tout autre document relatif au recours',
            ],
        ];

        foreach ($typeDocuments as $type) {
            TypeDocument::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}