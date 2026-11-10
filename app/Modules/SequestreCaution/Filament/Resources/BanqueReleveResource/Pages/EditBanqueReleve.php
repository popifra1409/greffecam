<?php

namespace App\Modules\SequestreCaution\Filament\Resources\BanqueReleveResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\BanqueReleveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBanqueReleve extends EditRecord
{
    protected static string $resource = BanqueReleveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
