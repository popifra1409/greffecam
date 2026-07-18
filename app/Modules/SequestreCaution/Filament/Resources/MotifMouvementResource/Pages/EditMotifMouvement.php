<?php

namespace App\Modules\SequestreCaution\Filament\Resources\MotifMouvementResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\MotifMouvementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMotifMouvement extends EditRecord
{
    protected static string $resource = MotifMouvementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
