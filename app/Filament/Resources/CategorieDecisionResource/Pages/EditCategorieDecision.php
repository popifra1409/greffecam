<?php

namespace App\Filament\Resources\CategorieDecisionResource\Pages;

use App\Filament\Resources\CategorieDecisionResource;
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
