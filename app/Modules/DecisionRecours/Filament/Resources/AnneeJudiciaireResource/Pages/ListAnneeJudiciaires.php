<?php

namespace App\Modules\DecisionRecours\Filament\Resources\AnneeJudiciaireResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\AnneeJudiciaireResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnneeJudiciaires extends ListRecords
{
    protected static string $resource = AnneeJudiciaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
