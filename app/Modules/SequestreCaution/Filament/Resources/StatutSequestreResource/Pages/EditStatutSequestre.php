<?php

namespace App\Modules\SequestreCaution\Filament\Resources\StatutSequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\StatutSequestreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatutSequestre extends EditRecord
{
    protected static string $resource = StatutSequestreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
