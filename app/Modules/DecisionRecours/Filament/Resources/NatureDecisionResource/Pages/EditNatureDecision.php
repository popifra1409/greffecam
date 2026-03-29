<?php

namespace App\Modules\DecisionRecours\Filament\Resources\NatureDecisionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\NatureDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNatureDecision extends EditRecord
{
    protected static string $resource = NatureDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
