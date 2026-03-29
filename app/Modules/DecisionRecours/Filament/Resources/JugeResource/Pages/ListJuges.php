<?php

namespace App\Modules\DecisionRecours\Filament\Resources\JugeResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\JugeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJuges extends ListRecords
{
    protected static string $resource = JugeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
