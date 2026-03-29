<?php

namespace App\Modules\DecisionRecours\Filament\Resources\AnneeJudiciaireResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\AnneeJudiciaireResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnneeJudiciaire extends EditRecord
{
    protected static string $resource = AnneeJudiciaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
