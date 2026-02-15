<?php

namespace App\Filament\Resources\RecoursResource\Pages;

use App\Filament\Resources\RecoursResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRecours extends CreateRecord
{
    protected static string $resource = RecoursResource::class;

    protected function afterCreate(): void
    {
        // Calculer automatiquement la date limite
        $this->record->calculerDateLimite();
        
        // Initialiser les 11 étapes du workflow
        $this->record->initialiserEtapes();
        
        // Marquer automatiquement la recevabilité
        $this->record->marquerRecevabilite();
    }
}