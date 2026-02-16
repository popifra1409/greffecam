<?php

namespace App\Filament\Resources\TransmissionDecisionResource\Pages;

use App\Filament\Resources\TransmissionDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransmissionDecision extends EditRecord
{
    protected static string $resource = TransmissionDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
