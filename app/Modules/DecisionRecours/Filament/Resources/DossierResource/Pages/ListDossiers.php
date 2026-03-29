<?php

namespace App\Modules\DecisionRecours\Filament\Resources\DossierResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\DossierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDossiers extends ListRecords
{
    protected static string $resource = DossierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
