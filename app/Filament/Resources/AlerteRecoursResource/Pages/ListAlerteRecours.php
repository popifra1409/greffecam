<?php

namespace App\Filament\Resources\AlerteRecoursResource\Pages;

use App\Filament\Resources\AlerteRecoursResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlerteRecours extends ListRecords
{
    protected static string $resource = AlerteRecoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
