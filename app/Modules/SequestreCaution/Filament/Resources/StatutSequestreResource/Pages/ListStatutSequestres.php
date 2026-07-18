<?php

namespace App\Modules\SequestreCaution\Filament\Resources\StatutSequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\StatutSequestreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStatutSequestres extends ListRecords
{
    protected static string $resource = StatutSequestreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
