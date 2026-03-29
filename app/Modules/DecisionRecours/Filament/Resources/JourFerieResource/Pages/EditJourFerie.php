<?php

namespace App\Modules\DecisionRecours\Filament\Resources\JourFerieResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\JourFerieResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJourFerie extends EditRecord
{
    protected static string $resource = JourFerieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
