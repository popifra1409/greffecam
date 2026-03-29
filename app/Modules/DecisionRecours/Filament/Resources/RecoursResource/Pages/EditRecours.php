<?php

namespace App\Modules\DecisionRecours\Filament\Resources\RecoursResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\RecoursResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecours extends EditRecord
{
    protected static string $resource = RecoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
