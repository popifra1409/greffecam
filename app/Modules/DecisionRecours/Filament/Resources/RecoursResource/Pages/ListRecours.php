<?php

namespace App\Modules\DecisionRecours\Filament\Resources\RecoursResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\RecoursResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecours extends ListRecords
{
    protected static string $resource = RecoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
