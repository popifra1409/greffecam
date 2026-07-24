<?php

namespace App\Modules\DecisionRecours\Filament\Resources\GradeResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\GradeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGrade extends EditRecord
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
