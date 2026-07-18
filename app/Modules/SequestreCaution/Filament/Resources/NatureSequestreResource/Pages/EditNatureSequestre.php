<?php

namespace App\Modules\SequestreCaution\Filament\Resources\NatureSequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\NatureSequestreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNatureSequestre extends EditRecord
{
    protected static string $resource = NatureSequestreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
