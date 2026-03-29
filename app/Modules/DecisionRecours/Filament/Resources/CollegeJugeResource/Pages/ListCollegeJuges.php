<?php

namespace App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollegeJuges extends ListRecords
{
    protected static string $resource = CollegeJugeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
