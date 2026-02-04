<?php

namespace App\Filament\Resources\InfractionResource\Pages;

use App\Filament\Resources\InfractionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInfraction extends EditRecord
{
    protected static string $resource = InfractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
