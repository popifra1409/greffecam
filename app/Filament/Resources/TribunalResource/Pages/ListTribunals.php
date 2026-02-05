<?php

namespace App\Filament\Resources\TribunalResource\Pages;

use App\Filament\Resources\TribunalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTribunals extends ListRecords
{
    protected static string $resource = TribunalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
