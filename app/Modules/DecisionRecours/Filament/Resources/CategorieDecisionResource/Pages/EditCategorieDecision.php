<?php

namespace App\Modules\DecisionRecours\Filament\Resources\CategorieDecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\CategorieDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategorieDecision extends EditRecord
{
    protected static string $resource = CategorieDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
