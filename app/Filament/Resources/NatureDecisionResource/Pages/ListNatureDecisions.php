<?php

namespace App\Filament\Resources\NatureDecisionResource\Pages;

use App\Filament\Resources\NatureDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNatureDecisions extends ListRecords
{
    protected static string $resource = NatureDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
