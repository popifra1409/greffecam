<?php

namespace App\Filament\Resources\JourFerieResource\Pages;

use App\Filament\Resources\JourFerieResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJourFeries extends ListRecords
{
    protected static string $resource = JourFerieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
