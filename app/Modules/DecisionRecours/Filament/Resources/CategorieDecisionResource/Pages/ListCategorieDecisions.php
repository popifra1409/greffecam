<?php

namespace App\Modules\DecisionRecours\Filament\Resources\CategorieDecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\CategorieDecisionResource;
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
