<?php

namespace App\Filament\Resources\NatureDecisionResource\Pages;

use App\Filament\Resources\NatureDecisionResource;
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
