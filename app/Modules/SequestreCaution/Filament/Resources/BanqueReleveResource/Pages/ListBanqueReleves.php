<?php

namespace App\Modules\SequestreCaution\Filament\Resources\BanqueReleveResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\BanqueReleveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBanqueReleves extends ListRecords
{
    protected static string $resource = BanqueReleveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('➕ Nouveau relevé'),
        ];
    }
}
