<?php

namespace App\Modules\DecisionRecours\Filament\Resources\DossierResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\DossierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDossier extends CreateRecord
{
    protected static string $resource = DossierResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Générer automatiquement le numéro de dossier
        $data['numero_dossier'] = \App\Models\Dossier::genererNumeroDossier(
            $data['tribunal_id'],
            $data['section_id'],
            $data['matiere_id'],
            now()->year
        );

        // Définir l'utilisateur qui enrôle
        $data['enrole_par'] = auth()->id();

        return $data;
    }
}
