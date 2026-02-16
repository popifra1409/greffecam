<?php

namespace App\Filament\Resources\TransmissionDecisionResource\Pages;

use App\Filament\Resources\TransmissionDecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransmissionDecisions extends ListRecords
{
    protected static string $resource = TransmissionDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
