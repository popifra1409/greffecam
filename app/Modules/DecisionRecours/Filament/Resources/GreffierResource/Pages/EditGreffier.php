<?php

namespace App\Modules\DecisionRecours\Filament\Resources\GreffierResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\GreffierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGreffier extends EditRecord
{
    protected static string $resource = GreffierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
