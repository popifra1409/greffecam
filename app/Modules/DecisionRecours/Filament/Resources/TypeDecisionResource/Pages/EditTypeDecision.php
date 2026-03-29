<?php

namespace App\Modules\DecisionRecours\Filament\Resources\TypeDecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\TypeDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeDecision extends EditRecord
{
    protected static string $resource = TypeDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
