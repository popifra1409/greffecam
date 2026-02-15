<?php

namespace App\Filament\Resources\TypeSectionResource\Pages;

use App\Filament\Resources\TypeSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeSection extends EditRecord
{
    protected static string $resource = TypeSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
