<?php

namespace App\Modules\DecisionRecours\Filament\Resources\SectionResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\SectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSections extends ListRecords
{
    protected static string $resource = SectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
