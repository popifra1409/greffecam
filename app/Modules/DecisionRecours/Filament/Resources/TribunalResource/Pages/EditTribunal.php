<?php

namespace App\Modules\DecisionRecours\Filament\Resources\TribunalResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\TribunalResource;
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
