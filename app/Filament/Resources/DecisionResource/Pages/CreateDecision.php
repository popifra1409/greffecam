<?php

namespace App\Filament\Resources\DecisionResource\Pages;

use App\Filament\Resources\DecisionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDecision extends CreateRecord
{
    protected static string $resource = DecisionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Définir le créateur comme greffier responsable et détenteur actuel
        $data['greffier_responsable_id'] = $data['greffier_responsable_id'] ?? auth()->id();
        $data['detenteur_actuel_id'] = auth()->id();

        return $data;
    }
}
