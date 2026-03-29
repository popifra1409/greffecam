<?php

namespace App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\CollegeJugeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollegeJuge extends EditRecord
{
    protected static string $resource = CollegeJugeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
