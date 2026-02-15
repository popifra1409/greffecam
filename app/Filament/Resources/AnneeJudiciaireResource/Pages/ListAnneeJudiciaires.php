<?php

namespace App\Filament\Resources\AnneeJudiciaireResource\Pages;

use App\Filament\Resources\AnneeJudiciaireResource;
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
