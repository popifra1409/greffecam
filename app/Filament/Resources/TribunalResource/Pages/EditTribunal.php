<?php

namespace App\Filament\Resources\TribunalResource\Pages;

use App\Filament\Resources\TribunalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTribunal extends EditRecord
{
    protected static string $resource = TribunalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
