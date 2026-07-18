<?php

namespace App\Modules\SequestreCaution\Filament\Resources\NatureSequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\NatureSequestreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNatureSequestres extends ListRecords
{
    protected static string $resource = NatureSequestreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
