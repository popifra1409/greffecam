<?php

namespace App\Modules\DecisionRecours\Filament\Resources\TypeRecoursResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\TypeRecoursResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeRecours extends EditRecord
{
    protected static string $resource = TypeRecoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
