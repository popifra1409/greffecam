<?php

namespace App\Modules\DecisionRecours\Filament\Resources\GreffierResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\GreffierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGreffiers extends ListRecords
{
    protected static string $resource = GreffierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
