<?php

namespace App\Filament\Resources\CategorieDecisionResource\Pages;

use App\Filament\Resources\CategorieDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategorieDecisions extends ListRecords
{
    protected static string $resource = CategorieDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
