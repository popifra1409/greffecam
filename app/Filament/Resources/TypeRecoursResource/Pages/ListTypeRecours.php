<?php

namespace App\Filament\Resources\TypeRecoursResource\Pages;

use App\Filament\Resources\TypeRecoursResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeRecours extends ListRecords
{
    protected static string $resource = TypeRecoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
