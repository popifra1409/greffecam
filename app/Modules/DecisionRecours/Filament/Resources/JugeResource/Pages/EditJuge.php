<?php

namespace App\Modules\DecisionRecours\Filament\Resources\JugeResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\JugeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJuge extends EditRecord
{
    protected static string $resource = JugeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
